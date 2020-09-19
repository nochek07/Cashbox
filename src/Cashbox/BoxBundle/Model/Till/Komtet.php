<?php

namespace Cashbox\BoxBundle\Model\Till;

use Cashbox\BoxBundle\Model\Report\TillModelReport;
use Cashbox\BoxBundle\Model\Type\TillTypes;
use Cashbox\BoxBundle\Service\Mailer;
use Komtet\KassaSdk\{Check, Client, Payment, Position, QueueManager, Vat};

/**
 * Class Komtet
 *
 * @see https://github.com/Komtet/komtet-kassa-php-sdk
 */
class Komtet extends AbstractTill
{
    protected $name = TillTypes::TILL_TYPE_KOMTET;

    /**
     * @var QueueManager
     */
    private $queueManager = null;

    /**
     * {@inheritDoc}
     */
    public function connect(): bool
    {
        if (is_null($this->queueManager)) {
            try {
                $komtet = $this->tillDocument->getData();
                $client = new Client($komtet['shop_id'], $komtet['secret']);
                $this->queueManager = new QueueManager($client);
                $this->queueManager->registerQueue($komtet['queue_name'], $komtet['queue_id']);
            } catch (\Exception $e) {
                return false;
            }
        }
        return true;
    }

    /**
     * Building data for Komtet
     *
     * {@inheritDoc}
     *
     * @example
     * [
     *      "order"  => "1224",
     *      "email"  => "1@mail.ru",
     *      "action" => "action" (sale|refund),
     *      "kkm"    => [
     *          "positions" => [
     *              0 => [
     *                  "name"     => "Name",
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
    public function buildData(array $param): array
    {
        return [
            "order" => $param['order'],
            "email" => $param['email'],
            "action" => TillTypes::TILL_ACTION_SALE,
            "kkm" => [
                "positions" => [
                    0 => [
                        "name" => sprintf($this->organizationDocument->getPatternNomenclature(), $param['order']),
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
     * {@inheritDoc}
     */
    public function send(array $data, string $type)
    {
        $komtet = $this->tillDocument->getData();
        $this->queueManager->setDefaultQueue($komtet['queue_name']);

        $tax_system = $komtet['tax_system'];
        switch ($data["action"]) {
            case TillTypes::TILL_ACTION_SALE:
                $check = Check::createSell($data["order"], $data["email"], $tax_system);
                break;
            case TillTypes::TILL_ACTION_REFUND:
                $check = Check::createSellReturn($data["order"], $data["email"], $tax_system);
                break;
            default:
                return 'actionError';
        }

        // Говорим, что чек нужно распечатать
        $check->setShouldPrint(true);

        $vat = new Vat($komtet['vat']);

        foreach ($data["kkm"]["positions"] as $value) {
            // Позиция в чеке: имя, цена, кол-во, общая стоимость, скидка, налог
            $position = new Position(
                $value["name"],
                (float)$value["price"],
                (int)$value["quantity"],
                (float)$value["orderSum"],
                $vat
            );
            $check->addPosition($position);
        }

        // Итоговая сумма расчёта
        if (isset($data["kkm"]["payment"]["card"])) {
            $payment = new Payment(Payment::TYPE_CARD, (float)$data["kkm"]["payment"]["card"]);
            $check->addPayment($payment);
        }
        if (isset($data["kkm"]["payment"]["cash"])) {
            $payment = new Payment(Payment::TYPE_CASH, (float)$data["kkm"]["payment"]["cash"]);
            $check->addPayment($payment);
        }

        $tin = $this->organizationDocument->getTin();

        // Добавляем чек в очередь
        try {
            $request = $this->queueManager->putCheck($check);
            if (isset($res['state'])) {
                $this->getReport()->add(new TillModelReport(), [
                    'type' => $this->name,
                    'typePayment' => $type,
                    'state' => TillTypes::TILL_STATE_NEW,
                    'dataTill' => $request,
                    'dataPost' => $data,
                    'action' => $data['action'],
                    'tin' => $tin
                ]);

                $this->sendMail($data, $type);
                return '';
            } else {
                $this->getReport()->add(new TillModelReport(), [
                    'type' => $this->name,
                    'typePayment' => $type,
                    'state' => TillTypes::TILL_STATE_OTHER_ERROR,
                    'dataTill' => $request,
                    'action' => TillTypes::TILL_ACTION_ERROR,
                    'tin' => $tin
                ]);
            }
        } catch (\Exception $error) {
            $this->getReport()->add(new TillModelReport(), [
                'type' => $this->name,
                'typePayment' => $type,
                'state' => TillTypes::TILL_STATE_ERROR,
                'dataTill' => ["error_description" => $error->getMessage()],
                'action' => TillTypes::TILL_ACTION_ERROR,
                'tin' => $tin
            ]);
            return $error->getMessage();
        }

        return TillTypes::TILL_STATE_OTHER_ERROR;
    }

    /**
     * {@inheritDoc}
     */
    public function sendMail(array $data, string $type): bool
    {
        $mailer = $this->getMailer();
        if ($mailer instanceof Mailer) {
            $email = $this->organizationDocument->getAdminEmail();
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
                $data['typePayment'] = $type;
                $data['type'] = $this->name;

                $mailer->send($email, $data);
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
	public function isQueueActive($name): bool
    {
        if ($this->queueManager instanceof QueueManager) {
            return $this->queueManager->isQueueActive($name);
        } else {
            return false;
        }
	}

    /**
     * Check Komtet Queue
     *
     * @return false
     */
    public function checkTill(): bool
    {
        $komtet = $this->tillDocument->getData();
        if ($komtet['cancel_action'] == 1) {
            if ($this->connect()) {
                if (!$this->isQueueActive($komtet['queue_name'])) {
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }
}