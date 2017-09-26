<?php

namespace Cashbox\BoxBundle\Controller;

use Komtet\KassaSdk\Check;
use Komtet\KassaSdk\Client;
use Komtet\KassaSdk\Payment;
use Komtet\KassaSdk\Position;
use Komtet\KassaSdk\QueueManager;
use Komtet\KassaSdk\Vat;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Cashbox\BoxBundle\Services\MongoDB;

class DefaultController extends Controller
{
    CONST MSG_CASHBOX_UNAV = "Касса не доступна";
    CONST MSG_ERROR        = "Ошибка";
    CONST MSG_ERROR_HASH   = "Ошибка в контрольнной сумме";
    CONST MSG_ERROR_INN    = "Неправильно выбрана организация";
    CONST MSG_ERROR_CHECK  = "Чек уже пробит ранее";

    /**
     * @var QueueManager $manager
     */
    private $manager = null;

    /**
     * @Route("/test")
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testAction(Request $request) {

        $repository = $this->get('doctrine_mongodb')
            ->getManager()
            ->getRepository('BoxBundle:ReportKomtet');

        $report = $repository->findOneBy(
            array(
                'type'   => 'site'
            )
        );

        echo '<pre>';
        print_r($report);
        echo '</pre>';


        return new Response('qqq');
    }

    /**
     * Отправка чека из 1С
     * @Route("/send1c", schemes={"https"})
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function send1cAction(Request $request)
    {
        if($request->isMethod(Request::METHOD_POST)) {

            if($request->getContentType()==='json') {
                $postData = file_get_contents('php://input');
                $data = json_decode($postData, true);

                if(!is_null($data)) {
                    if($data["INN"]==$this->container->getParameter('INN')) {
                        if ($this->check1cMD5($data)) {
                            $client = new Client($this->container->getParameter('komtet_shop_id'), $this->container->getParameter('komtet_secret'));
                            $this->manager = new QueueManager($client);

                            $komtet_cashbox_name = $this->container->getParameter('komtet_cashbox_name');
                            $this->manager->registerQueue($komtet_cashbox_name, $this->container->getParameter('komtet_cashbox_id'));

                            if (!$this->manager->isQueueActive($komtet_cashbox_name)) {
                                return new Response($this->buildResponse('For1C', 0, 100, null, self::MSG_CASHBOX_UNAV));
                            } else {
                                $report = $this->container->get("mongodb.cashbox")
                                    ->find1cReport($data["action"], $data["uuid"]);
                                if(is_null($report)) {
                                    $error = $this->sendKKM($data, MongoDB::ERROR_FROM_1C);
                                    if($error==='')
                                        return new Response($this->buildResponse('For1C', 0, 0, null, null));
                                } else {
                                    $error = self::MSG_ERROR_CHECK;
                                }
                                return new Response($this->buildResponse('For1C', 0, 100, null, $error));
                            }
                        } else {
                            return new Response($this->buildResponse('For1C', 0, 100, null, self::MSG_ERROR_HASH));
                        }
                    } else {
                        return new Response($this->buildResponse('For1C', 0, 100, null, self::MSG_ERROR_INN));
                    }
                }
            }
        }

        return new Response($this->buildResponse('For1C', 0, 100, null, self::MSG_ERROR));
    }

    /**
     * Проверка сайта/очереди из 1С
     * @Route("/chek1c", schemes={"https"})
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function chek1cAction(Request $request)
    {
        if($request->isMethod(Request::METHOD_POST)) {
            if($request->getContentType()==='json') {
                $client = new Client($this->container->getParameter('komtet_shop_id'), $this->container->getParameter('komtet_secret'));
                $this->manager = new QueueManager($client);

                $komtet_cashbox_name = $this->container->getParameter('komtet_cashbox_name');
                $this->manager->registerQueue($komtet_cashbox_name, $this->container->getParameter('komtet_cashbox_id'));

                if (!$this->manager->isQueueActive($komtet_cashbox_name)) {
                    return new Response($this->buildResponse('For1C', 0, 100, null, self::MSG_CASHBOX_UNAV));
                } else {
                    return new Response($this->buildResponse('For1C', 0, 0, null, null));
                }
            }
        }

        return new Response($this->buildResponse('For1C', 0, 100, null, self::MSG_ERROR));
    }

    /**
     * Checking the MD5 sign.
     * @param  array $data payment parameters
     * @return bool true if MD5 hash is correct
     */
    private function check1cMD5($data) {
        $hash = $data["action"].';'.$this->container->getParameter('handling_secret').';';
        if(isset($data["kkm"]["payment"]["card"])){
            $hash .= $data["kkm"]["payment"]["card"].';';
        }
        if(isset($data["kkm"]["payment"]["cash"])){
            $hash .= $data["kkm"]["payment"]["cash"].';';
        }
        $hash .= $data["order"].';'.$data["INN"].';';

        if(strtolower(md5($hash))==$data["hash"])
            return true;
        else
            return false;
    }

    /**
     * Отправка данных на кассу через Komtet
     * @param array $data - массив с данными
     * [
     *      "order"  => "1224",
     *      "email"  => "1@mail.ru",
     *      "action" => "sale",
     *      "kkm"    => [
     *          "positions" => [
     *              0 => [
     *                  "name"     => "Наименование",
     *                  "price"    => 3.0,
     *                  "quantity" => 2,
     *                  "orderSum" => 6.0,
     *                  "discount" => 0
     *               ]
     *           ],
     *           "payment" => [
     *              "cash" => 2.0,
     *              "card" => 4.0
     *           ]
     *      ]
     * ]
     * @param $from - источник чека
     * @return string
     */
    private function sendKKM($data, $from)
    {
        $komtet_cashbox_name = $this->container->getParameter('komtet_cashbox_name');
        if(is_null($this->manager)) {
            $client = new Client($this->container->getParameter('komtet_shop_id'), $this->container->getParameter('komtet_secret'));
            $this->manager = new QueueManager($client);

            $this->manager->registerQueue($komtet_cashbox_name, $this->container->getParameter('komtet_cashbox_id'));
        }
        $this->manager->setDefaultQueue($komtet_cashbox_name);

        $system_vat = $this->container->getParameter('system_vat');
        $check = null;
        switch ($data["action"]) {
            case "sale":
                $check = Check::createSell($data["order"], $data["email"], $system_vat);
            break;
            case "refund":
                $check = Check::createSellReturn($data["order"], $data["email"], $system_vat);
            break;
        }

        // Говорим, что чек нужно распечатать
        $check->setShouldPrint(true);

        $vat = new Vat($this->container->getParameter('vat'));

        foreach ($data["kkm"]["positions"] as $value) {
            // Позиция в чеке: имя, цена, кол-во, общая стоимость, скидка, налог
            $position = new Position($value["name"], (float)$value["price"], (int)$value["quantity"], (float)$value["orderSum"], (float)$value["discount"], $vat);
            $check->addPosition($position);
        }

        // Итоговая сумма расчёта
        if(isset($data["kkm"]["payment"]["card"])) {
            $payment = Payment::createCard((float)$data["kkm"]["payment"]["card"]);
            $check->addPayment($payment);
        }
        if(isset($data["kkm"]["payment"]["cash"])) {
            $payment = Payment::createCash((float)$data["kkm"]["payment"]["cash"]);
            $check->addPayment($payment);
        }

        $mongodb_cashbox = $this->container->get("mongodb.cashbox");

        // Добавляем чек в очередь.
        try {
            $res = $this->manager->putCheck($check);
            if(isset($res['state'])) {
                $mongodb_cashbox->setErrorSuccess($from, $res['state'], $res, $data);
                $this->sendReport($data, $from);
                return '';
            } else {
                $mongodb_cashbox->setErrorSuccess($from, 'otherError', $res);
            }
        } catch (\Exception $e) {
            $mongodb_cashbox->setErrorSuccess(
                $from,
                'error',
                array(
                    "error_description" => $e->getMessage()
                )
            );

            return $e->getMessage();
        }

        return 'otherError';
    }

    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->redirect($this->container->getParameter('redirect_url'));
    }

    /**
     * Отправка данных на электронную почту администратора
     * @param array $data - массив с данными
     * @param $from - источник чека
     */
    private function sendReport($data, $from) {
        if(isset($data["kkm"]["payment"]["card"])) {
            $card = (float)$data["kkm"]["payment"]["card"];
        } else {
            $card = 0;
        }
        if(isset($data["kkm"]["payment"]["cash"])) {
            $cash = (float)$data["kkm"]["payment"]["cash"];
        } else {
            $cash = 0;
        }

        try {
            $message = \Swift_Message::newInstance()
                ->setSubject('Кассовая операция')
                ->setFrom($this->container->getParameter('mailer_user'))
                ->setTo($this->container->getParameter('admin_email'))
                ->setBody(
                    $this->renderView(
                        'BoxBundle:Default:email.text.twig',
                        array(
                            'action' => $data['action'],
                            'type'   => $from,
                            'email'  => $data['email'],
                            'order'  => $data['order'],
                            'cash'   => $cash,
                            'cart'   => $card,
                        )
                    )
                )
            ;
            $this->get('mailer')->send($message);
        } catch (\Exception $e) {
        }
    }

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

                $mongodb_cashbox = $this->container->get("mongodb.cashbox");
                $mongodb_cashbox->setYandexTransaction(
                    $request->get('action'),
                    $orderSum,
                    $email,
                    $request->request->all()
                );

                $order = $request->get('customerNumber');

                $data = [
                    "order"  => $order,
                    "email"  => $email,
                    "action" => 'sale',
                    "kkm" => [
                        "positions" => [
                            0 => [
                                "name"     => sprintf($this->container->getParameter('nomenclature'), $order),
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
                $this->sendKKM($data, MongoDB::ERROR_FROM_SITE);

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

                if($this->container->getParameter('cancel_action')) {
                    $client = new Client($this->container->getParameter('komtet_shop_id'), $this->container->getParameter('komtet_secret'));
                    $this->manager = new QueueManager($client);

                    $komtet_cashbox_name = $this->container->getParameter('komtet_cashbox_name');
                    $this->manager->registerQueue($komtet_cashbox_name, $this->container->getParameter('komtet_cashbox_id'));

                    if (!$this->manager->isQueueActive($komtet_cashbox_name)) {
                        return new Response($this->buildResponse($request->get('action'), $request->get('invoiceId'), 100, $this->container->getParameter('shop_yandex_id'), self::MSG_CASHBOX_UNAV));
                    }
                }

                $mongodb_cashbox = $this->container->get("mongodb.cashbox");
                $mongodb_cashbox->setYandexTransaction(
                    $request->get('action'),
                    (float)$request->get('orderSumAmount'),
                    $request->get('email'),
                    $request->request->all()
                );

                $response = $this->getAnswer($request);
            }
        }

        return new Response($response);
    }

    /**
     * Handles "checkOrder" and "paymentAviso" requests.
     * @param  \Symfony\Component\HttpFoundation\Request $request payment parameters
     * @return string prepared XML response
     */
    public function processRequest(Request $request) {
        //Проверка
        if (!$this->checkMD5($request)) {
            return $this->buildResponse($request->get('action'), $request->get('invoiceId'), 1, $this->container->getParameter('shop_yandex_id'));
        }
        return '';
    }


    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request payment parameters
     * @return string prepared XML response
     */
    private function getAnswer(Request $request) {
        return $this->buildResponse($request->get('action'), $request->get('invoiceId'), 0, $this->container->getParameter('shop_yandex_id'));
    }

    /**
     * Building XML response.
     * @param  string $functionName  "checkOrder" or "paymentAviso" string
     * @param  string $invoiceId     transaction number
     * @param  string $result_code   result code
     * @param  string $shopId        shop Id
     * @param  string $message       error message. May be null.
     * @return string                prepared XML response
     */
    private function buildResponse($functionName, $invoiceId, $result_code, $shopId = null, $message = null) {
        try {
            $performedDatetime = self::formatDate(new \DateTime());
            $response = '<?xml version="1.0" encoding="UTF-8"?><' . $functionName . 'Response performedDatetime="' . $performedDatetime .
                '" code="' . $result_code . '" ' . ($message != null ? 'message="' . $message . '"' : "") . ' invoiceId="' . $invoiceId . '" shopId="' . $shopId . '"/>';
            return $response;
        } catch (\Exception $e) {

        }
        return '';
    }

    public static function formatDate(\DateTime $date) {
        $performedDatetime = $date->format("Y-m-d") . "T" . $date->format("H:i:s") . ".000" . $date->format("P");
        return $performedDatetime;
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
            $request->get('invoiceId') . ";" . $request->get('customerNumber') . ";" . $this->container->getParameter('shop_yandex_secret');
        $md5 = strtoupper(md5($str));
        if ($md5 != strtoupper($request->get('md5'))) {
            return false;
        }
        /*Дополнительная проверка*/
        if ($request->get('h') != md5($request->get('customerNumber')."_".$request->get('orderSumAmount')."_z") ) {
            return false;
        }
        return true;
    }
}
