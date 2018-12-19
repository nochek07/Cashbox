<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Services\Komtet;
use Cashbox\BoxBundle\Services\MongoDB;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SberbankController extends Controller
{
    //CONST GATEWAY_URL = 'https://3dsec.sberbank.ru/payment/rest/';
    //CONST FORM_URL    = 'https://3dsec.sberbank.ru/payment/merchants/z-tec/payment_ru.html';

    CONST GATEWAY_URL = 'https://securepayments.sberbank.ru/payment/rest/';
    CONST FORM_URL    = 'https://securepayments.sberbank.ru/payment/merchants/sbersafe/payment_ru.html';
    CONST ORDER_DAY   = 7;

    /**
     * @Route("/restSberbank", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function restSberbankAction(Request $request) {
        $failUrl      = $this->getSiteUrl($request, 0);
        $redirect_url = $failUrl;
        if($request->isMethod(Request::METHOD_POST)) {

            if (Komtet::otherCheckMD5($request, $this->getParameter('handling_secret'))) {

                $komtet = $this->getParameter('komtet');
                if($komtet['cancel_action']) {
                    $manager = $this->get("komtet.cashbox");

                    if (!$manager->isQueueActive($komtet['komtet_cashbox_name'])) {
                        return $this->redirect($failUrl);
                    }
                }

                $successUrl = $this->getSiteUrl($request, 1);

                $data = array(
                    'userName' => $this->getParameter('sberbank_username'),
                    'password' => $this->getParameter('sberbank_password'),
                    'orderNumber' => $request->get('customerNumber'),
                    'amount' => $this->replaceSum($request->get('Sum')),
                    'returnUrl' => $successUrl,
                    'failUrl' => $failUrl,
                    'jsonParams' => '{"email": "'.$request->get('email').'"}',
                    'expirationDate' => date("Y-m-d\TH:i:s", strtotime('+'.self::ORDER_DAY.' day'))
                );

                $answer = $this->gateway('register.do', $data);

                if(isset($answer['errorCode']) && ($answer['errorCode']==0 || $answer['errorCode']==1)) {
                    //Заказ уже существует
                    $data = array(
                        'userName' => $this->getParameter('sberbank_username'),
                        'password' => $this->getParameter('sberbank_password'),
                        'orderNumber' => $request->get('customerNumber')
                    );
                    $response = $this->gateway('getOrderStatusExtended.do', $data);

                    if($response['errorCode']==0) {
                        // Произведена полная оплата
                        if($response['orderStatus']==2) {
                            $redirect_url = $successUrl;
                        } else {
                            $id = $response['attributes'][0]['value'];
                            $redirect_url = self::FORM_URL.'?mdOrder='.$id;
                        }
                    }

                    echo $redirect_url;
                }elseif(isset($answer['formUrl'])) {
                    $redirect_url = $answer['formUrl'];
                }
            }
        }

        return $this->redirect($redirect_url);
    }

    /**
     * @Route("/callbackSberbank", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function callbackSberbankAction(Request $request)
    {
        if($request->isMethod(Request::METHOD_GET)) {
            if ($this->checkCallback($request)) {

                $customerNumber = $request->query->get('orderNumber');
                $data = array(
                    'userName' => $this->getParameter('sberbank_username'),
                    'password' => $this->getParameter('sberbank_password'),
                    'orderNumber' => $customerNumber
                );
                $response = $this->gateway('getOrderStatusExtended.do', $data);
                if(isset($response['errorCode']) && $response['errorCode']==0 && $response['actionCode']==0) {

                    $email = '';
                    if(sizeof($response['merchantOrderParams'])) {
                        foreach ($response['merchantOrderParams'] as $param) {
                            if ($param['name']=='email'){
                                $email = $param['value'];
                                break;
                            }
                        }
                    }

                    $orderSum = ((float)$response['amount']) / 100;

                    $mongodb_cashbox = $this->get("mongodb.cashbox");
                    $mongodb_cashbox->setSberbankTransaction(
                        $orderSum,
                        $customerNumber,
                        $email,
                        $response
                    );
					
					$komtet = $this->getParameter('komtet');

                    $data = [
                        "order"  => $customerNumber,
                        "email"  => $email,
                        "action" => 'sale',
                        "kkm" => [
                            "positions" => [
                                0 => [
                                    "name"     => sprintf($komtet['nomenclature'], $customerNumber),
                                    "price"    => $orderSum,
                                    "quantity" => 1,
                                    "orderSum" => $orderSum,
                                    "discount" => 0
                                ]
                            ],
                            "payment" => [
                                "card" => $orderSum
                            ]
                        ]
                    ];
                    $manager = $this->get("komtet.cashbox");
                    $manager->sendKKM($data, MongoDB::ERROR_FROM_SBER);
                }
            }
        }

        return new Response('');
    }

    /**
     * Основная функция работы со сбербанком
     *
     * @param string $method
     * @param array $data
     * @return mixed
     */
    private function gateway($method, $data) {
        $curl = curl_init(); // Инициализируем запрос
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::GATEWAY_URL.$method, // Полный адрес метода
            CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
            CURLOPT_POST => true, // Метод POST
            CURLOPT_POSTFIELDS => http_build_query($data) // Данные в запросе
        ));
        $response = curl_exec($curl); // Выполненяем запрос

        $response = json_decode($response, true); // Декодируем из JSON в массив
        curl_close($curl); // Закрываем соединение

        return $response; // Возвращаем ответ
    }

    /**
     * @param string $Sum
     * @return string
     */
    private function replaceSum($Sum){
        $pos1 = strpos($Sum, '.');
        $pos2 = strpos($Sum, ',');
        if ($pos1 === false && $pos2 === false) {
            $Sum .= '00';
        }
        $Sum = str_replace('.', '', $Sum);
        $Sum = str_replace(',', '', $Sum);
        $Sum = str_replace(' ', '', $Sum);
        return (string) $Sum;
    }

    /**
     * @param  Request $request
     * @param int $success
     * @return string
     */
    private function getSiteUrl(Request $request, $success = 0){
        $referer = Request::create(
            $request->headers->get('referer'),
            'GET',
            array('success' => $success)
        );

        return $referer->getUri();
    }

    /**
     * @param  Request $request
     * @return bool
     */
    private function checkCallback(Request $request){
        $params = $request->query->all();
        ksort($params);
        reset($params);
        $str = '';
        foreach ($params as $key => $value) {
            if($key!=='checksum') {
                $str .= $key.';'.$value.';';
            }
        }

        if($str!==''){
            if (strtolower(hash_hmac ( 'sha256' , $str , $this->getParameter('sberbank_secret') )) == strtolower($params['checksum']))
                return true;
        }
        return false;
    }
}