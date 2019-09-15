<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Document\Payment;
use Cashbox\BoxBundle\Model\KKM\KKMInterface;
use Cashbox\BoxBundle\Model\Report\SberbankReport;
use Cashbox\BoxBundle\Model\Type\PaymentTypes;
use Symfony\Component\HttpFoundation\Request;

class SberbankPayment extends PaymentAbstract
{
    protected $name = 'Sberbank';

    /**
     * Test merchants
     * @example const GATEWAY_URL = 'https://3dsec.sberbank.ru/payment/rest/';
     * @example const FORM_URL    = 'https://3dsec.sberbank.ru/payment/merchants/{login}/payment_ru.html';
     */

    const GATEWAY_URL = 'https://securepayments.sberbank.ru/payment/rest/';
    const FORM_URL    = 'https://securepayments.sberbank.ru/payment/merchants/sbersafe/payment_ru.html';

    /**
     * The number of days valid order
     */
    const ORDER_DAY = 7;

    /**
     * {@inheritDoc}
     * @return string
     */
    public function send(Request $request)
    {
        $payment = $this->getDesiredPayment(
            $this->Organization->getPayments()
        );
        if ($payment instanceof Payment) {
            $sberbank = $payment->getData();
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
                    if (sizeof($response['merchantOrderParams']) > 0) {
                        foreach ($response['merchantOrderParams'] as $param) {
                            if ($param['name'] == 'email') {
                                $email = $param['value'];
                                break;
                            }
                        }
                    }

                    $orderSum = ((float)$response['amount']) / 100;

                    $this->getReport()->add(new SberbankReport(), [
                        'orderSum' => $orderSum,
                        'customerNumber' => $customerNumber,
                        'email' => $email,
                        'inn' => $this->Organization->getINN(),
                        'data' => $response
                    ]);

                    $kkm = $this->getKkmByPayment($payment);
                    if ($kkm instanceof KKMInterface) {
                        if ($kkm->connect()) {
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
        }
        return '';
    }

    /**
     * {@inheritDoc}
     * @return string
     */
    public function check(Request $request)
    {
        return '';
    }

    /**
     * Вычисление hash-суммы
     *
     * @param Request $request
     * @param array $param
     * @return bool
     */
    private function checkCallback(Request $request, array $param)
    {
        $params = $request->query->all();
        ksort($params);
        reset($params);
        $str = '';
        foreach ($params as $key => $value) {
            if ($key !== 'checksum' && $key !== 'inn') {
                $str .= $key . ';' . $value . ';';
            }
        }

        if ($str!=='') {
            if (strtolower(hash_hmac('sha256', $str, $param['secret'])) == strtolower($params['checksum'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Основная функция работы со сбербанком
     *
     * @param string $method
     * @param array $data
     * @return mixed
     */
    private function gateway(string $method, array $data)
    {
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
     * Получение ссылки для перенаправления
     *
     * @param Request $request
     * @param string $failUrl - ссылка в случае ошибки
     * @return string
     */
    public function getRedirectUrl(Request $request, string $failUrl)
    {
        $redirect_url = $failUrl;
        $payment = $this->getDesiredPayment(
            $this->Organization->getPayments()
        );
        if ($payment instanceof Payment) {
            if ($this->otherCheckMD5($request, $this->Organization->getSecret())) {
                $kkm = $this->getKkmByPayment($payment);
                if ($kkm instanceof KKMInterface) {
                    if (!$kkm->checkKKM()) {
                        return $redirect_url;
                    }
                }

                $successUrl = $this->getSiteUrl($request, 1);

                $sberbank = $payment->getData();
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
                    // Заказ уже существует
                    $data = [
                        'userName' => $sberbank['sberbank_username'],
                        'password' => $sberbank['sberbank_password'],
                        'orderNumber' => $request->get('customerNumber')
                    ];
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
        }
        return $redirect_url;
    }

    /**
     * Получение ссулки с которой перешел пользователь
     *
     * @param Request $request
     * @param int $success
     * @return string
     */
    public function getSiteUrl(Request $request, int $success = 0)
    {
        $referer = Request::create(
            $request->headers->get('referer'),
            'GET',
            ['success' => $success]
        );

        return $referer->getUri();
    }

    /**
     * Преоброзование строки с суммой
     *
     * @param string $Sum
     * @return string
     */
    private function replaceSum(string $Sum)
    {
        $pos1 = strpos($Sum, '.');
        $pos2 = strpos($Sum, ',');
        if ($pos1 === false && $pos2 === false) {
            $Sum .= '00';
        }
        return str_replace(' ', '', str_replace(',', '', str_replace('.', '', $Sum)));
    }
}