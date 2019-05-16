<?php

namespace Cashbox\BoxBundle\Model\KKM;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\Report\KomtetReport;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Komtet\KassaSdk\{Check, Client, Payment, Position, QueueManager, Vat};

/**
 * Class Komtet
 *
 * @package Cashbox\BoxBundle\Model\KKM
 *
 * @see https://github.com/Komtet/komtet-kassa-php-sdk
 */
class Komtet extends KKMAbstract
{
    /**
     * @var QueueManager $QueueManager
     */
    private $QueueManager = null;

    /**
     * @var array $komtet
     */
    protected $komtet = [];

    /**
     * Komtet constructor.
     *
     * @param Organization $Organization
     * @param ManagerRegistry $manager
     */
    public function __construct(Organization $Organization, ManagerRegistry $manager)
    {
        parent::__construct($Organization, $manager);
        
        $this->komtet = $Organization->getDataKomtet();
    }

    /**
     * @return bool
     */
    public function connect()
    {
        if(is_null($this->QueueManager)) {
            try {
                $client = new Client($this->komtet['shop_id'], $this->komtet['secret']);
                $this->QueueManager = new QueueManager($client);
                $this->QueueManager->registerQueue($this->komtet['queue_name'], $this->komtet['queue_id']);
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Отправка данных на кассу через Komtet
     *
     * @param array $param
     *
     * @return array $data - массив с данными
     * @example
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
     */
    public function buildData(array $param)
    {
        return [
            "order" => $param['order'],
            "email" => $param['email'],
            "action" => 'sale',
            "kkm" => [
                "positions" => [
                    0 => [
                        "name" => sprintf($this->getOrganization()->getPatternNomenclature(), $param['order']),
                        "price" => $param['orderSum'],
                        "quantity" => 1,
                        "orderSum" => $param['orderSum'],
                        "discount" => 0
                    ]
                ],
                "payment" => [
                    "card" => $param['orderSum']
                ]
            ]
        ];
    }

    /**
     * Отправка данных на кассу через Komtet
     *
     * @param array $data - массив с данными
     * @param string $from - источник чека
     * @return string
     */
    public function send(array $data, string $from)
    {
        $this->QueueManager->setDefaultQueue($this->komtet['queue_name']);

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
            $position = new Position(
                $value["name"],
                (float)$value["price"],
                (int)$value["quantity"],
                (float)$value["orderSum"],
                (float)$value["discount"], $vat
            );
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

        $INN = $this->getOrganization()->getINN();
        $KomtetReport = new KomtetReport($this->getManager());

        // Добавляем чек в очередь.
        try {
            $request = $this->QueueManager->putCheck($check);
            if(isset($res['state'])) {
                $KomtetReport->create([
                    'type' => $from,
                    'state' => $request['state'],
                    'dataKomtet' => $request,
                    'dataPost' => $data,
                    'inn' => $INN
                ]);

                $this->sendMail($data, $from);
                return '';
            } else {
                $KomtetReport->create([
                    'type' => $from,
                    'state' => 'otherError',
                    'dataKomtet' => $request,
                    'inn' => $INN
                ]);
            }
        } catch (\Exception $error) {
            $KomtetReport->create([
                'type' => $from,
                'state' => 'error',
                'dataKomtet' => ["error_description" => $error->getMessage()],
                'inn' => $INN
            ]);

            return $error->getMessage();
        }

        return 'otherError';
    }

    /**
     * Отправка данных на электронную почту администратора
     *
     * @param array $data - массив с данными
     * @param string $from - источник чека
     * @return bool
     */
    public function sendMail(array $data, string $from)
    {
        $mailer = $this->getMailer();
        if (!is_null($mailer)) {
            $email = $this->getOrganization()->getAdminEmail();
            if (trim($email) !== '') {
                if (isset($data["kkm"]["payment"]["card"])) {
                    $data['card'] = (float)$data["kkm"]["payment"]["card"];
                } else {
                    $data['card'] = 0;
                }
                if (isset($data["kkm"]["payment"]["cash"])) {
                    $data['cash'] = (float)$data["kkm"]["payment"]["cash"];
                } else {
                    $data['cash'] = 0;
                }
                $data['type'] = $from;

                $mailer->send($email, $data);

                return true;
            }
        }

        return false;
    }

    /**
     * Проверка очереди комтет-кассы
     *
     * @param string $name - имя очереди
     * @return bool
     */
	public function isQueueActive($name)
    {
        if(!is_null($this->QueueManager)) {
            return $this->QueueManager->isQueueActive($name);
        } else {
            return false;
        }
	}
}