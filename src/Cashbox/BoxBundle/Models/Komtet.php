<?php

namespace Cashbox\BoxBundle\Models;

use Cashbox\BoxBundle\Document\Organization;
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

    /**
     * @var array $komtet
     */
    protected $komtet = [];

    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @var Organization $organization
     */
    private $organization;

    /**
     * Komtet constructor.
     * @param Organization $organization
     * @param ContainerInterface $container
     */
    public function __construct(Organization $organization, ContainerInterface $container)
    {
        $this->organization = $organization;
        $this->container = $container;
        $this->komtet = $organization->getDataKomtet();
        if(is_null($this->manager)) {
            $client = new Client($this->komtet['shop_id'], $this->komtet['secret']);
            $this->manager = new QueueManager($client);
            $this->manager->registerQueue($this->komtet['queue_name'], $this->komtet['queue_id']);
        }
    }

    /**
     * Отправка данных на кассу через Komtet
     *
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
        $this->manager->setDefaultQueue($this->komtet['queue_name']);

        $tax_system = $this->komtet['tax_system'];
        $check = null;
        switch ($data["action"]) {
            case "sale":
                $check = Check::createSell($data["order"], $data["email"], $tax_system);
                break;
            case "refund":
                $check = Check::createSellReturn($data["order"], $data["email"], $tax_system);
                break;
        }

        // Говорим, что чек нужно распечатать
        $check->setShouldPrint(true);

        $vat = new Vat($this->komtet['vat']);

        foreach ($data["kkm"]["positions"] as $value) {
            // Позиция в чеке: имя, цена, кол-во, общая стоимость, скидка, налог
            $position = new Position($value["name"], (float)$value["price"], (int)$value["quantity"], (float)$value["orderSum"], (float)$value["discount"], $vat);
            $check->addPosition($position);
        }

        // Итоговая сумма расчёта
        if(isset($data["kkm"]["payment"]["card"])) {
            $payment = new Payment(Payment::TYPE_CARD, (float)$data["kkm"]["payment"]["card"]);
            $check->addPayment($payment);
        }
        if(isset($data["kkm"]["payment"]["cash"])) {
            $payment = new Payment(Payment::TYPE_CASH, (float)$data["kkm"]["payment"]["cash"]);
            $check->addPayment($payment);
        }

        $cashbox_mongodb = $this->container->get('mongodb.cashbox');

        // Добавляем чек в очередь.
        try {
            $res = $this->manager->putCheck($check);
            if(isset($res['state'])) {
                $cashbox_mongodb->setReportKomtet([
                    'type' => $from,
                    'state' => $res['state'],
                    'dataKomtet' => $res,
                    'dataPost' => $data,
                    'inn' => $this->organization->getINN()
                ]);

                $this->sendReport($data, $from);
                return '';
            } else {
                $cashbox_mongodb->setReportKomtet([
                    'type' => $from,
                    'state' => 'otherError',
                    'dataKomtet' => $res,
                    'inn' => $this->organization->getINN()
                ]);
            }
        } catch (\Exception $error) {
            $cashbox_mongodb->setReportKomtet([
                'type' => $from,
                'state' => 'error',
                'dataKomtet' => ["error_description" => $error->getMessage()],
                'inn' => $this->organization->getINN()
            ]);

            return $error->getMessage();
        }

        return 'otherError';
    }

    /**
     * Отправка данных на электронную почту администратора
     *
     * @param array $data - массив с данными
     * @param $from - источник чека
     */
    private function sendReport($data, $from) {
        $email = $this->organization->getAdminEmail();
        if (trim($email)!=='') {
            if (isset($data["kkm"]["payment"]["card"])) {
                $card = (float)$data["kkm"]["payment"]["card"];
            } else {
                $card = 0;
            }
            if (isset($data["kkm"]["payment"]["cash"])) {
                $cash = (float)$data["kkm"]["payment"]["cash"];
            } else {
                $cash = 0;
            }

            try {
                $message = \Swift_Message::newInstance()
                    ->setSubject('Кассовая операция')
                    ->setFrom($this->container->getParameter("mailer_user"))
                    ->setTo($email)
                    ->setBody(
                        $this->container->get('templating')->render(
                            'BoxBundle:Default:email.text.twig',
                            [
                                'action' => $data['action'],
                                'type' => $from,
                                'email' => $data['email'],
                                'order' => $data['order'],
                                'cash' => $cash,
                                'cart' => $card,
                            ]
                        )
                    );
                $this->container->get('mailer')->send($message);
            } catch (\Exception $error) {
            }
        }
    }

    /**
     * Проверка очереди комтет-кассы
     *
     * @param $name - имя очереди
     * @return bool
     */
	public function isQueueActive($name){
		return $this->manager->isQueueActive($name);
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
    public static function buildResponse($functionName, $invoiceId, $result_code, $shopId = null, $message = null) {
        try {
            $performedDatetime = self::formatDate(new \DateTime());
            $response = '<?xml version="1.0" encoding="UTF-8"?><' . $functionName . 'Response performedDatetime="' . $performedDatetime .
                '" code="' . $result_code . '" ' . ($message != null ? 'message="' . $message . '"' : "") . ' invoiceId="' . $invoiceId . '" shopId="' . $shopId . '"/>';
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
    public static function formatDate(\DateTime $date) {
        return $date->format("Y-m-d") . "T" . $date->format("H:i:s") . ".000" . $date->format("P");
    }

    /**
     * Дополнительная проверка
     *
     * @param Request $request
     * @param String $handling_secret
     * @return bool
     */
    public static function otherCheckMD5(Request $request, $handling_secret){
        return ( $request->get('h') != md5($request->get('customerNumber') . "_" . $request->get('orderSumAmount') . "_" . $handling_secret) );
    }
}