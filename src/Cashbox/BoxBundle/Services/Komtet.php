<?php

namespace Cashbox\BoxBundle\Services;

use Komtet\KassaSdk\Check;
use Komtet\KassaSdk\Client;
use Komtet\KassaSdk\Payment;
use Komtet\KassaSdk\Position;
use Komtet\KassaSdk\QueueManager;
use Komtet\KassaSdk\Vat;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class Komtet
{
    CONST MSG_CASHBOX_UNAV = "Касса не доступна";
    CONST MSG_ERROR        = "Ошибка";
    CONST MSG_ERROR_HASH   = "Ошибка в контрольнной сумме";
    CONST MSG_ERROR_INN    = "Неправильно выбрана организация";
    CONST MSG_ERROR_CHECK  = "Чек уже был пробит ранее";

    /**
     * @var QueueManager $manager
     */
    private $manager = null;

    private $mailer;
    private $templating;
    private $cashbox_mongodb;

    protected $komtet_params = array();
    protected $mailer_user;

    /**
     * Komtet constructor.
     * @param $komtet_params
     * @param $mailer_user
     * @param ContainerInterface $container
     */
    public function __construct($komtet_params, $mailer_user, ContainerInterface $container)
    {
        $this->komtet_params   = $komtet_params;
        $this->mailer_user     = $mailer_user;
        if(is_null($this->manager)) {
            $client = new Client($this->komtet_params['komtet_shop_id'], $this->komtet_params['komtet_secret']);
            $this->manager = new QueueManager($client);
            $this->manager->registerQueue($this->komtet_params['komtet_cashbox_name'], $this->komtet_params['komtet_cashbox_id']);
        }

        $this->mailer          = $container->get('mailer');
        $this->templating      = $container->get('templating');
        $this->cashbox_mongodb = $container->get('mongodb.cashbox');
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
    public function sendKKM($data, $from)
    {
        $komtet_cashbox_name = $this->komtet_params['komtet_cashbox_name'];
        $this->manager->setDefaultQueue($komtet_cashbox_name);

        $system_vat = $this->komtet_params['system_vat'];
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

        $vat = new Vat($this->komtet_params['vat']);

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

        // Добавляем чек в очередь.
        try {
            $res = $this->manager->putCheck($check);
            if(isset($res['state'])) {
                $this->cashbox_mongodb->setErrorSuccess($from, $res['state'], $res, $data);
                $this->sendReport($data, $from);
                return '';
            } else {
                $this->cashbox_mongodb->setErrorSuccess($from, 'otherError', $res);
            }
        } catch (\Exception $e) {
            $this->cashbox_mongodb->setErrorSuccess(
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
                ->setFrom($this->mailer_user)
                ->setTo($this->komtet_params['admin_email'])
                ->setBody(
                    $this->templating->render(
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
            $this->mailer->send($message);
        } catch (\Exception $e) {
        }
    }

    /**
     * Проверка очереди комтет-кассы
     * @param $name - имя очереди
     * @return bool
     */
	public function isQueueActive($name){
		return $this->manager->isQueueActive($name);
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
    public static function buildResponse($functionName, $invoiceId, $result_code, $shopId = null, $message = null) {
        try {
            $performedDatetime = self::formatDate(new \DateTime());
            $response = '<?xml version="1.0" encoding="UTF-8"?><' . $functionName . 'Response performedDatetime="' . $performedDatetime .
                '" code="' . $result_code . '" ' . ($message != null ? 'message="' . $message . '"' : "") . ' invoiceId="' . $invoiceId . '" shopId="' . $shopId . '"/>';
            return $response;
        } catch (\Exception $e) {

        }
        return '';
    }

    /**
     * Форматирование даты
     * @param \DateTime $date
     * @return string
     */
    public static function formatDate(\DateTime $date) {
        return $date->format("Y-m-d") . "T" . $date->format("H:i:s") . ".000" . $date->format("P");
    }

    /**
     * Дополнительная проверка
     * @param Request $request
     * @param String $handling_secret
     * @return bool
     */
    public static function otherCheckMD5(Request $request, $handling_secret){
        return ( $request->get('h') != md5($request->get('customerNumber')."_".$request->get('orderSumAmount')."_".$handling_secret) );
    }
}