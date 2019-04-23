<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\Komtet;
use Cashbox\BoxBundle\Model\OrganizationModel;
use Cashbox\BoxBundle\Services\MongoDB;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Используется устаревший API Яндекса
 * https://tech.yandex.ru/money/doc/payment-solution/payment-notifications/payment-notifications-check-docpage/
 *
 * Новый
 * https://kassa.yandex.ru/docs/guides/#bankowskaq-karta
 */
class YandexPayment extends PaymentAbstract
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * SberbankPayment constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function send(Request $request)
    {
        $responseText = '';
        if($request->isMethod(Request::METHOD_POST)) {
            /**
             * @var Organization $Organization
             */
            $Organization = OrganizationModel::getOrganization($request, $this->container->get('doctrine_mongodb'));
            if (!is_null($Organization)) {
                $yandex = $Organization->getDataYandex();
                $responseText = $this->processRequest($request, $yandex, $Organization->getSecret());
                if ($responseText == '') {
                    $email = $request->get('email');
                    $orderSum = (float)$request->get('orderSumAmount');
                    $order = $request->get('customerNumber');

                    $mongodb_cashbox = $this->container->get("mongodb.cashbox");
                    $mongodb_cashbox->setYandexTransaction([
                        'action' => $request->get('action'),
                        'orderSum' => $orderSum,
                        'customerNumber' => $order,
                        'email' => $email,
                        'inn' => $Organization->getINN(),
                        'data' => $request->request->all()
                    ]);

                    $data = [
                        "order" => $order,
                        "email" => $email,
                        "action" => 'sale',
                        "kkm" => [
                            "positions" => [
                                0 => [
                                    "name" => sprintf($Organization->getPatternNomenclature(), $order),
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

                    $KomtetObj = new Komtet($Organization, $this->container);
                    $KomtetObj->sendKKM($data, MongoDB::ERROR_FROM_SITE);

                    $responseText = $this->getAnswer($request, $yandex);
                }
            }
        }

        return $responseText;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function check(Request $request)
    {
        $responseText = '';
        if($request->isMethod(Request::METHOD_POST)) {
            /**
             * @var Organization $Organization
             */
            $Organization = OrganizationModel::getOrganization($request, $this->container->get('doctrine_mongodb'));
            if (!is_null($Organization)) {
                $yandex = $Organization->getDataYandex();
                $responseText = $this->processRequest($request, $yandex, $Organization->getSecret());
                if ($responseText == '') {
                    $komtet = $Organization->getDataKomtet();
                    if ($komtet['cancel_action']==1) {
                        $KomtetObj = new Komtet($Organization, $this->container);
                        if (!$KomtetObj->isQueueActive($komtet['queue_name'])) {
                            return new Response($this->buildResponse($request->get('action'), $request->get('invoiceId'), 100, $yandex['shop_yandex_id'], Komtet::MSG_CASHBOX_UNAV));
                        }
                    }

                    $mongodb_cashbox = $this->container->get("mongodb.cashbox");
                    $mongodb_cashbox->setYandexTransaction([
                        'action' => $request->get('action'),
                        'orderSum' => (float)$request->get('orderSumAmount'),
                        'customerNumber' => $request->get('customerNumber'),
                        'email' => $request->get('email'),
                        'inn' => $Organization->getINN(),
                        'data' => $request->request->all()
                    ]);

                    $responseText = $this->getAnswer($request, $yandex);
                }
            }
        }

        return $responseText;
    }

    /**
     * @param Request $request
     * @param array $param
     * @return string
     */
    private function getAnswer(Request $request, array $param) {
        return $this->buildResponse($request->get('action'), $request->get('invoiceId'), 0, $param['yandex_id']);
    }

    /**
     * Handles "checkOrder" and "paymentAviso" requests.
     *
     * @param  Request $request
     * @param  array $param
     * @param  string $secret
     * @return string
     */
    private function processRequest(Request $request, array $param, string $secret) {
        //Проверка
        if (!$this->checkMD5($request, $param, $secret)) {
            return $this->buildResponse($request->get('action'), $request->get('invoiceId'), 1, $param['yandex_id']);
        }
        return '';
    }

    /**
     * Checking the MD5 sign.
     *
     * @param  Request $request
     * @param  array $param
     * @param  string $secret
     * @return bool (true if MD5 hash is correct)
     */
    private function checkMD5(Request $request, array $param, string $secret) {
        $str = $request->get('action') . ";" .
            $request->get('orderSumAmount') . ";" . $request->get('orderSumCurrencyPaycash') . ";" .
            $request->get('orderSumBankPaycash') . ";" . $request->get('shopId') . ";" .
            $request->get('invoiceId') . ";" . $request->get('customerNumber') . ";" . $param['secret'];
        $md5 = strtoupper(md5($str));
        if ($md5 != strtoupper($request->get('md5'))) {
            return false;
        }

        if($this->otherCheckMD5($request, $secret))
            return false;
        else
            return true;
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
    public function buildResponse($functionName, $invoiceId, $result_code, $shopId = null, $message = null) {
        try {
            $performedDatetime = self::formatDate(new \DateTime());
            $response = '<?xml version="1.0" encoding="UTF-8"?><' . $functionName . 'Response performedDatetime="' . $performedDatetime .
                '" code="' . $result_code . '" ' . ($message != null ? 'message="' . $message . '"' : "") . ' invoiceId="' . $invoiceId .
                '" shopId="' . $shopId . '"/>';
            return $response;
        } catch (\Exception $error) {
        }
        return '';
    }

    /**
     * Форматирование даты
     *
     * @param \DateTime $date
     * @return string
     */
    private function formatDate(\DateTime $date) {
        return $date->format("Y-m-d") . "T" . $date->format("H:i:s") . ".000" . $date->format("P");
    }
}