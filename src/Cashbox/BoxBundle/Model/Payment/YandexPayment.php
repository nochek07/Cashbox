<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\KKM\{KKMInterface, KKMMessages};
use Cashbox\BoxBundle\Model\Payment\Exception\KKMException;
use Cashbox\BoxBundle\Model\Report\YandexReport;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class YandexPayment
 *
 * @see Используется устаревший API Яндекса
 * https://tech.yandex.ru/money/doc/payment-solution/payment-notifications/payment-notifications-check-docpage/
 *
 * @see Новый API Яндекса
 * https://tech.yandex.ru/money/doc/payment-solution/payment-notifications/payment-notifications-check-docpage/
 */
class YandexPayment extends PaymentAbstract
{
    /**
     * {@inheritDoc}
     * @return string
     */
    public function send(Request $request, Organization $Organization, $kkm = null)
    {
        $yandex = $Organization->getDataYandex();
        $responseText = $this->processRequest($request, $yandex, $Organization->getSecret());
        if ($responseText == '') {
            $email = $request->get('email');
            $orderSum = (float)$request->get('orderSumAmount');
            $order = $request->get('customerNumber');

            $YandexReport = new YandexReport($this->manager);
            $YandexReport->create([
                'action' => $request->get('action'),
                'orderSum' => $orderSum,
                'customerNumber' => $order,
                'email' => $email,
                'inn' => $Organization->getINN(),
                'data' => $request->request->all()
            ]);

            if ($kkm instanceof KKMInterface) {
                if ($kkm->connect()) {
                    $dataKKM = $kkm->buildData([
                        "order" => $order,
                        "email" => $email,
                        "orderSum" => $orderSum
                    ]);
                    $kkm->send($dataKKM, PaymentTypes::PAYMENT_TYPE_YANDEX);
                }
            }

            $responseText = $this->getAnswer($request, $yandex);
        }

        return $responseText;
    }

    /**
     * {@inheritDoc}
     * @return string
     */
    public function check(Request $request, Organization $Organization, $kkm = null)
    {
        $yandex = $Organization->getDataYandex();
        $responseText = $this->processRequest($request, $yandex, $Organization->getSecret());
        if ($responseText == '') {
            $komtet = $Organization->getDataKomtet();
            if ($komtet['cancel_action']==1) {
                try {
                    $this->checkKKM($komtet['queue_name'], $kkm);
                } catch (KKMException $error) {
                    return $this->buildResponse($request->get('action'), $request->get('invoiceId'), 100, $yandex['shop_yandex_id'], KKMMessages::MSG_CASHBOX_UNAV);
                }
            }

            $YandexReport = new YandexReport($this->manager);
            $YandexReport->create([
                'action' => $request->get('action'),
                'orderSum' => (float)$request->get('orderSumAmount'),
                'customerNumber' => $request->get('customerNumber'),
                'email' => $request->get('email'),
                'inn' => $Organization->getINN(),
                'data' => $request->request->all()
            ]);

            $responseText = $this->getAnswer($request, $yandex);
        }

        return $responseText;
    }

    /**
     * Handles "checkOrder" and "paymentAviso" requests.
     *
     * @param  Request $request
     * @param  array $param
     * @param  string $secret
     * @return string
     */
    private function processRequest(Request $request, array $param, string $secret)
    {
        // Проверка
        if (!$this->checkMD5($request, $param, $secret)) {
            return $this->buildResponse($request->get('action'), $request->get('invoiceId'), 1, $param['yandex_id']);
        }
        return '';
    }

    /**
     * Получение ответа
     * 
     * @param Request $request
     * @param array $param
     * @return string
     */
    private function getAnswer(Request $request, array $param)
    {
        return $this->buildResponse($request->get('action'), $request->get('invoiceId'), 0, $param['yandex_id']);
    }

    /**
     * Checking the MD5 sign.
     *
     * @param  Request $request
     * @param  array $param
     * @param  string $secret
     * @return bool (true if MD5 hash is correct)
     */
    private function checkMD5(Request $request, array $param, string $secret)
    {
        $str = $request->get('action') . ";" .
            $request->get('orderSumAmount') . ";" . $request->get('orderSumCurrencyPaycash') . ";" .
            $request->get('orderSumBankPaycash') . ";" . $request->get('shopId') . ";" .
            $request->get('invoiceId') . ";" . $request->get('customerNumber') . ";" . $param['secret'];
        $md5 = strtoupper(md5($str));
        if ($md5 != strtoupper($request->get('md5'))) {
            return false;
        }

        return !$this->otherCheckMD5($request, $secret);
    }

    /**
     * Building XML response.
     *
     * @param  string $functionName  "checkOrder" or "paymentAviso" string
     * @param  string $invoiceId     transaction number
     * @param  string $result_code   result code
     * @param  string $shopId        shop Id
     * @param  string $message       error message. May be null.
     * @return string                prepared XML response
     */
    public function buildResponse($functionName, $invoiceId, $result_code, $shopId = null, $message = null)
    {
        try {
            $performedDatetime = self::formatDate(new \DateTime());
            $response = '<?xml version="1.0" encoding="UTF-8"?><' . $functionName . 'Response performedDatetime="' . $performedDatetime .
                '" code="' . $result_code . '" ' . (!is_null($message) ? 'message="' . $message . '"' : "") . ' invoiceId="' . $invoiceId .
                '" shopId="' . $shopId . '"/>';
            return $response;
        } catch (\Exception $error) {
            return '';
        }
    }

    /**
     * Форматирование даты
     *
     * @param \DateTime $date
     * @return string
     */
    private function formatDate(\DateTime $date)
    {
        return $date->format("Y-m-d") . "T" . $date->format("H:i:s") . ".000" . $date->format("P");
    }
}