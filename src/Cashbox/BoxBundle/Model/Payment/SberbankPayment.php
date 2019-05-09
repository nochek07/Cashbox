<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\KKM\KKMInterface;
use Cashbox\BoxBundle\Model\Report\SberbankReport;
use Symfony\Component\HttpFoundation\Request;

class SberbankPayment extends PaymentAbstract
{
    //CONST GATEWAY_URL = 'https://3dsec.sberbank.ru/payment/rest/';
    //CONST FORM_URL    = 'https://3dsec.sberbank.ru/payment/merchants/{login}/payment_ru.html';

    CONST GATEWAY_URL = 'https://securepayments.sberbank.ru/payment/rest/';
    CONST FORM_URL    = 'https://securepayments.sberbank.ru/payment/merchants/sbersafe/payment_ru.html';
    CONST ORDER_DAY   = 7;

    /**
     * @param Request $request
     * @param Organization $Organization
     * @param KKMInterface|null $kkm
     * @return string
     */
    public function send(Request $request, Organization $Organization, $kkm = null)
    {
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

                $SberbankReport = new SberbankReport($this->manager);
                $SberbankReport->create([
                    'orderSum' => $orderSum,
                    'customerNumber' => $customerNumber,
                    'email' => $email,
                    'inn' => $Organization->getINN(),
                    'data' => $response
                ]);

                if($kkm instanceof KKMInterface) {
                    if($kkm->connect()) {
                        $dataKKM = $kkm->buildData([
                            "order" => $customerNumber,
                            "email" => $email,
                            "orderSum" => $orderSum
                        ]);
                        $kkm->send($dataKKM, PaymentTypes::PAYMENT_TYPE_SBERBANK);
                    }
                }
            }
        }

        return '';
    }

    /**
     * @param Request $request
     * @param Organization $Organization
     * @param KKMInterface|null $kkm
     * @return string
     */
    public function check(Request $request, Organization $Organization, $kkm = null)
    {
        return '';
    }

    /**
     * @param Request $request
     * @param Organization $Organization
     * @param string $failUrl
     * @param KKMInterface|null $kkm
     * @return string
     */
    public function getRedirectUrl(Request $request, Organization $Organization, string $failUrl, $kkm = null)
    {
        $redirect_url = $failUrl;
        if ($this->otherCheckMD5($request, $Organization->getSecret())) {
            $komtet = $Organization->getDataKomtet();

            if ($komtet['cancel_action']==1 && $kkm instanceof KKMInterface) {
                if($kkm->connect()) {
                    if (!$kkm->isQueueActive($komtet['queue_name'])) {
                        return $redirect_url;
                    }
                } else {
                    return $redirect_url;
                }
            }

            $successUrl = $this->getSiteUrl($request, 1);

            $sberbank = $Organization->getDataSberbank();
            $data = [
                'userName' => $sberbank['sberbank_username'],
                'password' => $sberbank['sberbank_password'],
                'orderNumber' => $request->get('customerNumber'),
                'amount' => $this->replaceSum($request->get('Sum')),
                'returnUrl' => $successUrl,
                'failUrl' => $failUrl,
                'jsonParams' => '{"email": "' . $request->get('email') . '"}',
                'expirationDate' => date("Y-m-d\TH:i:s", strtotime('+' . self::ORDER_DAY . ' day'))
            ];

            $answer = $this->gateway('register.do', $data);

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
            } elseif (isset($answer['formUrl'])) {
                $redirect_url = $answer['formUrl'];
            }
        }

        return $redirect_url;
    }

    /**
     * Основная функция работы со сбербанком
     *
     * @param string $method
     * @param array $data
     * @return mixed
     */
    private function gateway(string $method, array $data) {
        $curl = curl_init(); // Инициализируем запрос
        curl_setopt_array($curl, [
            CURLOPT_URL => self::GATEWAY_URL . $method, // Полный адрес метода
            CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
            CURLOPT_POST => true, // Метод POST
            CURLOPT_POSTFIELDS => http_build_query($data) // Данные в запросе
        ]);
        $response = curl_exec($curl); // Выполненяем запрос

        $response = json_decode($response, true); // Декодируем из JSON в массив
        curl_close($curl); // Закрываем соединение

        return $response; // Возвращаем ответ
    }

    /**
     * @param string $Sum
     * @return string
     */
    private function replaceSum(string $Sum){
        $pos1 = strpos($Sum, '.');
        $pos2 = strpos($Sum, ',');
        if ($pos1 === false && $pos2 === false) {
            $Sum .= '00';
        }
        $Sum = str_replace(' ', '', str_replace(',', '', str_replace('.', '', $Sum)));
        return $Sum;
    }

    /**
     * @param Request $request
     * @param int $success
     * @return string
     */
    public function getSiteUrl(Request $request, int $success = 0){
        $referer = Request::create(
            $request->headers->get('referer'),
            'GET',
            ['success' => $success]
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