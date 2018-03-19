<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Services\Komtet;
use Cashbox\BoxBundle\Services\MongoDB;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class YandexController extends Controller
{
    /**
     * @Route("/aviso", schemes={"https"})
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function avisoAction(Request $request)
    {
        $response = '';
        if($request->isMethod(Request::METHOD_POST)) {
            $response = $this->processRequest($request);
            if($response=='') {
                $email    = $request->get('email');
                $orderSum = (float)$request->get('orderSumAmount');
                $order    = $request->get('customerNumber');

                $mongodb_cashbox = $this->get("mongodb.cashbox");
                $mongodb_cashbox->setYandexTransaction(
                    $request->get('action'),
                    $orderSum,
                    $order,
                    $email,
                    $request->request->all()
                );
				
				$komtet = $this->getParameter('komtet');

                $data = [
                    "order"  => $order,
                    "email"  => $email,
                    "action" => 'sale',
                    "kkm" => [
                        "positions" => [
                            0 => [
                                "name"     => sprintf($komtet['nomenclature'], $order),
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
                $manager->sendKKM($data, MongoDB::ERROR_FROM_SITE);

                $response = $this->getAnswer($request);
            }
        }

        return new Response($response);
    }

    /**
     * @Route("/check", schemes={"https"})
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkAction(Request $request)
    {
        $response = '';
        if($request->isMethod(Request::METHOD_POST)) {

            $response = $this->processRequest($request);
            if($response=='') {

                $komtet = $this->getParameter('komtet');
                if($komtet['cancel_action']) {
                    $manager = $this->get("komtet.cashbox");

                    if (!$manager->isQueueActive($komtet['komtet_cashbox_name'])) {
                        return new Response(Komtet::buildResponse($request->get('action'), $request->get('invoiceId'), 100, $this->getParameter('shop_yandex_id'), Komtet::MSG_CASHBOX_UNAV));
                    }
                }

                $mongodb_cashbox = $this->get("mongodb.cashbox");
                $mongodb_cashbox->setYandexTransaction(
                    $request->get('action'),
                    (float)$request->get('orderSumAmount'),
                    $request->get('customerNumber'),
                    $request->get('email'),
                    $request->request->all()
                );

                $response = $this->getAnswer($request);
            }
        }

        return new Response($response);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request payment parameters
     * @return string prepared XML response
     */
    private function getAnswer(Request $request) {
        return Komtet::buildResponse($request->get('action'), $request->get('invoiceId'), 0, $this->getParameter('shop_yandex_id'));
    }

    /**
     * Handles "checkOrder" and "paymentAviso" requests.
     * @param  \Symfony\Component\HttpFoundation\Request $request payment parameters
     * @return string prepared XML response
     */
    public function processRequest(Request $request) {
        //Проверка
        if (!$this->checkMD5($request)) {
            return Komtet::buildResponse($request->get('action'), $request->get('invoiceId'), 1, $this->getParameter('shop_yandex_id'));
        }
        return '';
    }

    /**
     * Checking the MD5 sign.
     * @param  Request $request payment parameters
     * @return bool true if MD5 hash is correct
     */
    private function checkMD5(Request $request) {
        $str = $request->get('action') . ";" .
            $request->get('orderSumAmount') . ";" . $request->get('orderSumCurrencyPaycash') . ";" .
            $request->get('orderSumBankPaycash') . ";" . $request->get('shopId') . ";" .
            $request->get('invoiceId') . ";" . $request->get('customerNumber') . ";" . $this->getParameter('shop_yandex_secret');
        $md5 = strtoupper(md5($str));
        if ($md5 != strtoupper($request->get('md5'))) {
            return false;
        }

        if(Komtet::otherCheckMD5($request, $this->getParameter('handling_secret'))) return false;

        return true;
    }
}