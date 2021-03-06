<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Document\Payment;
use Cashbox\BoxBundle\Model\{Report\TransactionModelReport, Till\TillInterface, Type\PaymentTypes};
use Symfony\Component\HttpFoundation\Request;

class SberbankPayment extends AbstractPayment
{
    protected $name = PaymentTypes::PAYMENT_TYPE_SBERBANK;

    /**
     * Test merchants
     *
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
     *
     * @return string
     */
    public function send(Request $request)
    {
        $payment = $this->getDesiredPayment(
            $this->organization->getPayments()
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

                    $this->getReport()->add(new TransactionModelReport(), [
                        'type' => $this->name,
                        'action' => 'send',
                        'orderSum' => $orderSum,
                        'customerNumber' => $customerNumber,
                        'email' => $email,
                        'tin' => $this->organization->getTin(),
                        'data' => $response
                    ]);

                    $till = $this->getTillByPayment($payment);
                    if ($till instanceof TillInterface) {
                        if ($till->connect()) {
                            $tillData = $till->buildData([
                                "order" => $customerNumber,
                                "email" => $email,
                                "orderSum" => $orderSum
                            ]);
                            $till->send($tillData, PaymentTypes::PAYMENT_TYPE_SBERBANK);
                        }
                    }
                }
            }
        }
        return '';
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function check(Request $request)
    {
        return '';
    }

    /**
     * Calculation of hash-sum
     *
     * @param Request $request
     * @param array $param
     *
     * @return bool
     */
    private function checkCallback(Request $request, array $param): bool
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

        if ($str !== '') {
            if (strtolower(hash_hmac('sha256', $str, $param['secret'])) == strtolower($params['checksum'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gateway fot Sberbank
     *
     * @param string $method
     * @param array $data
     *
     * @return mixed
     */
    private function gateway(string $method, array $data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => self::GATEWAY_URL . $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data)
        ]);
        $response = curl_exec($curl);

        $response = json_decode($response, true);
        curl_close($curl);

        return $response;
    }

    /**
     * Get Redirect Url
     *
     * @param Request $request
     * @param string $failUrl error url of page
     *
     * @return string
     */
    public function getRedirectUrl(Request $request, string $failUrl): string
    {
        $redirectUrl = $failUrl;
        $payment = $this->getDesiredPayment(
            $this->organization->getPayments()
        );
        if ($payment instanceof Payment) {
            if ($this->otherCheckMD5($request, $this->organization->getSecret())) {
                $till = $this->getTillByPayment($payment);
                if ($till instanceof TillInterface) {
                    if (!$till->checkTill()) {
                        return $redirectUrl;
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
                        // Full payment has been made
                        if ($response['orderStatus'] == 2) {
                            $redirectUrl = $successUrl;
                        } else {
                            $id = $response['attributes'][0]['value'];
                            $redirectUrl = self::FORM_URL . '?mdOrder=' . $id;
                        }
                    }
                } elseif (isset($answer['formUrl'])) {
                    $redirectUrl = $answer['formUrl'];
                }
            }
        }
        return $redirectUrl;
    }

    /**
     * Getting the link with which the user switched
     *
     * @param Request $request
     * @param int $success
     *
     * @return string
     */
    public function getSiteUrl(Request $request, int $success = 0): string
    {
        $referer = Request::create(
            $request->headers->get('referer'),
            'GET',
            ['success' => $success]
        );

        return $referer->getUri();
    }

    /**
     * Replace Sum
     *
     * @param string $sum
     *
     * @return string
     */
    private function replaceSum(string $sum): string
    {
        $pos1 = strpos($sum, '.');
        $pos2 = strpos($sum, ',');
        if ($pos1 === false && $pos2 === false) {
            $sum .= '00';
        }
        return str_replace(' ', '', str_replace(',', '', str_replace('.', '', $sum)));
    }
}