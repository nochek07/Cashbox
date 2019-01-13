<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Models\Komtet;
use Cashbox\BoxBundle\Models\OrganizationModel;
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
            /**
             * @var Organization $Organization
             */
            $Organization = OrganizationModel::getOrganization($request, $this->get('doctrine_mongodb'));
            if (!is_null($Organization)) {

                if (Komtet::otherCheckMD5($request, $Organization->getSecret())) {
                    $komtet = $Organization->getDataKomtet();

                    if ($komtet['cancel_action']==1) {
                        $KomtetObj = new Komtet($Organization, $this->get('service_container'));

                        if (!$KomtetObj->isQueueActive($komtet['queue_name'])) {
                            return $this->redirect($failUrl);
                        }
                    }

                    $successUrl = $this->getSiteUrl($request, 1);

                    $sberbank = $Organization->getDataSberbank();
                    $data = array(
                        'userName' => $sberbank['sberbank_username'],
                        'password' => $sberbank['sberbank_password'],
                        'orderNumber' => $request->get('customerNumber'),
                        'amount' => $this->replaceSum($request->get('Sum')),
                        'returnUrl' => $successUrl,
                        'failUrl' => $failUrl,
                        'jsonParams' => '{"email": "' . $request->get('email') . '"}',
                        'expirationDate' => date("Y-m-d\TH:i:s", strtotime('+' . self::ORDER_DAY . ' day'))
                    );

                    $answer = $this->gateway('register.do', $data);

//                    echo '<pre>';
//                    print_r($answer);
//                    echo '</pre>';

                    if (isset($answer['errorCode']) && ($answer['errorCode'] == 0 || $answer['errorCode'] == 1)) {
                        //Заказ уже существует
                        $data = array(
                            'userName' => $sberbank['sberbank_username'],
                            'password' => $sberbank['sberbank_password'],
                            'orderNumber' => $request->get('customerNumber')
                        );
                        $response = $this->gateway('getOrderStatusExtended.do', $data);

                        if ($response['errorCode'] == 0) {
                            // Произведена полная оплата
                            if ($response['orderStatus'] == 2) {
                                $redirect_url = $successUrl;
                            } else {
                                $id = $response['attributes'][0]['value'];
                                $redirect_url = self::FORM_URL . '?mdOrder=' . $id;
                            }
                        }

                        echo $redirect_url;
                    } elseif (isset($answer['formUrl'])) {
                        $redirect_url = $answer['formUrl'];
                    }
                }
            }
        }

        return $this->redirect($redirect_url);
        //return new Response('');
    }

    /**
     * @Route("/callbackSberbank", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function callbackSberbankAction(Request $request)
    {
        if($request->isMethod(Request::METHOD_GET)) {
            /**
             * @var Organization $Organization
             */
            $Organization = OrganizationModel::getOrganization($request, $this->get('doctrine_mongodb'));
            if (!is_null($Organization)) {
                $sberbank = $Organization->getDataSberbank();
                if ($this->checkCallback($request, $sberbank)) {
                    $customerNumber = $request->query->get('orderNumber');
                    $data = [
                        'userName' => $sberbank['sberbank_username'],
                        'password' => $sberbank['sberbank_password'],
                        'orderNumber' => $customerNumber
                    ];
                    $response = $this->gateway('getOrderStatusExtended.do', $data);
                    if (isset($response['errorCode']) && $response['errorCode'] == 0 && $response['actionCode'] == 0) {

                        $email = '';
                        if (sizeof($response['merchantOrderParams'])) {
                            foreach ($response['merchantOrderParams'] as $param) {
                                if ($param['name'] == 'email') {
                                    $email = $param['value'];
                                    break;
                                }
                            }
                        }

                        $orderSum = ((float)$response['amount']) / 100;

                        $mongodb_cashbox = $this->get("mongodb.cashbox");
                        $mongodb_cashbox->setSberbankTransaction([
                            'orderSum' => $orderSum,
                            'customerNumber' => $customerNumber,
                            'email' => $email,
                            'inn' => $Organization->getINN(),
                            'data' => $response
                        ]);

                        $data = [
                            "order" => $customerNumber,
                            "email" => $email,
                            "action" => 'sale',
                            "kkm" => [
                                "positions" => [
                                    0 => [
                                        "name" => sprintf($Organization->getPatternNomenclature(), $customerNumber),
                                        "price" => $orderSum,
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

                        $KomtetObj = new Komtet($Organization, $this->get('service_container'));
                        $KomtetObj->sendKKM($data, MongoDB::ERROR_FROM_SBER);
                    }
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
     * @param Request $request
     * @param array $param
     * @return bool
     */
    private function checkCallback(Request $request, array $param){
        $params = $request->query->all();
        ksort($params);
        reset($params);
        $str = '';
        foreach ($params as $key => $value) {
            if($key!=='checksum' && $key!=='inn') {
                $str .= $key.';'.$value.';';
            }
        }

        if($str!==''){
            if (strtolower(hash_hmac ( 'sha256' , $str , $param['secret'] )) == strtolower($params['checksum']))
                return true;
        }
        return false;
    }
}