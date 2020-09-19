<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Document\Payment;
use Cashbox\BoxBundle\Model\{Report\TransactionModelReport, Till\TillInterface, Till\TillMessages, Type\PaymentTypes};
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
class YandexPayment extends AbstractPayment
{
    protected $name = PaymentTypes::PAYMENT_TYPE_YANDEX;

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
            $yandex = $payment->getData();
            $responseText = $this->processRequest($request, $yandex, $this->organization->getSecret());
            if ($responseText == '') {
                $email = $request->get('email');
                $orderSum = (float)$request->get('orderSumAmount');
                $order = $request->get('customerNumber');

                $this->getReport()->add(new TransactionModelReport(), [
                    'type' => $this->name,
                    'action' => $request->get('action'),
                    'orderSum' => $orderSum,
                    'customerNumber' => $order,
                    'email' => $email,
                    'tin' => $this->organization->getTin(),
                    'data' => $request->request->all()
                ]);

                $till = $this->getTillByPayment($payment);
                if ($till instanceof TillInterface) {
                    if ($till->connect()) {
                        $tillData = $till->buildData([
                            "order" => $order,
                            "email" => $email,
                            "orderSum" => $orderSum
                        ]);
                        $till->send($tillData, PaymentTypes::PAYMENT_TYPE_YANDEX);
                    }
                }

                return $this->getAnswer($request, $yandex);
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
        $payment = $this->getDesiredPayment(
            $this->organization->getPayments()
        );
        if ($payment instanceof Payment) {
            $yandex = $payment->getData();

            $responseText = $this->processRequest($request, $yandex, $this->organization->getSecret());
            if ($responseText == '') {
                $till = $this->getTillByPayment($payment);
                if ($till instanceof TillInterface) {
                    if (!$till->checkTill()) {
                        return $this->buildResponse(
                            $request->get('action'),
                            $request->get('invoiceId'),
                            100,
                            $yandex['shop_yandex_id'],
                            TillMessages::MSG_CASHBOX_UNAV
                        );
                    }
                }

                $this->getReport()->add(new TransactionModelReport(), [
                    'type' => $this->name,
                    'action' => $request->get('action'),
                    'orderSum' => (float)$request->get('orderSumAmount'),
                    'customerNumber' => $request->get('customerNumber'),
                    'email' => $request->get('email'),
                    'tin' => $this->organization->getTin(),
                    'data' => $request->request->all()
                ]);

                return $this->getAnswer($request, $yandex);
            }
        }

        return '';
    }

    /**
     * Handles "checkOrder" and "paymentAviso" requests.
     *
     * @param Request $request
     * @param array $param
     * @param string $secret
     *
     * @return string
     */
    private function processRequest(Request $request, array $param, string $secret): string
    {
        // Check
        if (!$this->checkMD5($request, $param, $secret)) {
            return $this->buildResponse($request->get('action'), $request->get('invoiceId'), 1, $param['yandex_id']);
        }
        return '';
    }

    /**
     * Get Answer
     * 
     * @param Request $request
     * @param array $param
     *
     * @return string
     */
    private function getAnswer(Request $request, array $param): string
    {
        return $this->buildResponse($request->get('action'), $request->get('invoiceId'), 0, $param['yandex_id']);
    }

    /**
     * Checking the MD5 sign.
     *
     * @param Request $request
     * @param array $param
     * @param string $secret
     *
     * @return bool true if MD5 hash is correct
     */
    private function checkMD5(Request $request, array $param, string $secret): bool
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
     * @param string $functionName  "checkOrder" or "paymentAviso" string
     * @param string $invoiceId     transaction number
     * @param string $result_code   result code
     * @param string $shopId        shop Id
     * @param string $message       error message. May be null.
     *
     * @return string               prepared XML response
     */
    public function buildResponse(
        string $functionName,
        string $invoiceId,
        string $result_code,
        ?string $shopId = null,
        ?string $message = null): string
    {
        try {
            $performedDatetime = self::formatDate(new \DateTime());
            return '<?xml version="1.0" encoding="UTF-8"?><' . $functionName . 'Response performedDatetime="' . $performedDatetime .
                '" code="' . $result_code . '" ' . (!is_null($message) ? 'message="' . $message . '"' : "") . ' invoiceId="' . $invoiceId .
                '" shopId="' . $shopId . '"/>';
        } catch (\Exception $error) {
            return '';
        }
    }

    /**
     * Date formatting
     *
     * @param \DateTime $date
     *
     * @return string
     */
    private function formatDate(\DateTime $date): string
    {
        return $date->format("Y-m-d") . "T" . $date->format("H:i:s") . ".000" . $date->format("P");
    }
}