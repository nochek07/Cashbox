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

/**
 * Используется устаревший API Яндекса
 * https://tech.yandex.ru/money/doc/payment-solution/payment-notifications/payment-notifications-check-docpage/
 *
 * Новый
 * https://kassa.yandex.ru/docs/guides/#bankowskaq-karta
 */
class YandexController extends Controller
{
    /**
     * @Route("/aviso", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function avisoAction(Request $request)
    {
        $response = '';
        if($request->isMethod(Request::METHOD_POST)) {
            /**
             * @var Organization $Organization
             */
            $Organization = OrganizationModel::getOrganization($request, $this->get('doctrine_mongodb'));
            if (!is_null($Organization)) {
                $yandex = $Organization->getDataYandex();
                $response = $this->processRequest($request, $yandex, $Organization->getSecret());
                if ($response == '') {
                    $email = $request->get('email');
                    $orderSum = (float)$request->get('orderSumAmount');
                    $order = $request->get('customerNumber');

                    $mongodb_cashbox = $this->get("mongodb.cashbox");
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

                    $KomtetObj = new Komtet($Organization, $this->get('service_container'));
                    $KomtetObj->sendKKM($data, MongoDB::ERROR_FROM_SITE);

                    $response = $this->getAnswer($request, $yandex);
                }
            }
        }

        return new Response($response);
    }

    /**
     * @Route("/check", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function checkAction(Request $request)
    {
        $response = '';
        if($request->isMethod(Request::METHOD_POST)) {
            /**
             * @var Organization $Organization
             */
            $Organization = OrganizationModel::getOrganization($request, $this->get('doctrine_mongodb'));
            if (!is_null($Organization)) {
                $yandex = $Organization->getDataYandex();
                $response = $this->processRequest($request, $yandex, $Organization->getSecret());
                if ($response == '') {

                    $komtet = $Organization->getDataKomtet();
                    if ($komtet['cancel_action']==1) {
                        $KomtetObj = new Komtet($Organization, $this->get('service_container'));
                        if (!$KomtetObj->isQueueActive($komtet['queue_name'])) {
                            return new Response(Komtet::buildResponse($request->get('action'), $request->get('invoiceId'), 100, $yandex['shop_yandex_id'], Komtet::MSG_CASHBOX_UNAV));
                        }
                    }

                    $mongodb_cashbox = $this->get("mongodb.cashbox");
                    $mongodb_cashbox->setYandexTransaction([
                        'action' => $request->get('action'),
                        'orderSum' => (float)$request->get('orderSumAmount'),
                        'customerNumber' => $request->get('customerNumber'),
                        'email' => $request->get('email'),
                        'inn' => $Organization->getINN(),
                        'data' => $request->request->all()
                    ]);

                    $response = $this->getAnswer($request, $yandex);
                }
            }
        }

        return new Response($response);
    }

    /**
     * @param Request $request
     * @param array $param
     * @return string
     */
    private function getAnswer(Request $request, array $param) {
        return Komtet::buildResponse($request->get('action'), $request->get('invoiceId'), 0, $param['yandex_id']);
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
            return Komtet::buildResponse($request->get('action'), $request->get('invoiceId'), 1, $param['yandex_id']);
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

        if(Komtet::otherCheckMD5($request, $secret))
            return false;
        else
            return true;
    }
}