<?php

namespace Cashbox\BoxBundle\Model\KKM;

use Cashbox\BoxBundle\Model\Type\KKMTypes;
use Cashbox\BoxBundle\Service\Mailer;
use Cashbox\BoxBundle\Model\Report\KKMReport;
use Komtet\KassaSdk\{Check, Client, Payment, Position, QueueManager, Vat};

/**
 * Class Komtet
 *
 * @see https://github.com/Komtet/komtet-kassa-php-sdk
 */
class Komtet extends AbstractKKM
{
    protected $name = KKMTypes::KKM_TYPE_KOMTET;

    /**
     * @var QueueManager
     */
    private $QueueManager = null;

    /**
     * {@inheritDoc}
     */
    public function connect()
    {
        if (is_null($this->QueueManager)) {
            try {
                $komtet = $this->kkmDocument->getData();
                $client = new Client($komtet['shop_id'], $komtet['secret']);
                $this->QueueManager = new QueueManager($client);
                $this->QueueManager->registerQueue($komtet['queue_name'], $komtet['queue_id']);
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
            "action" => KKMTypes::KKM_ACTION_SALE,
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
        $komtet = $this->kkmDocument->getData();
        $this->QueueManager->setDefaultQueue($komtet['queue_name']);

        $tax_system = $komtet['tax_system'];
        switch ($data["action"]) {
            case KKMTypes::KKM_ACTION_SALE:
                $check = Check::createSell($data["order"], $data["email"], $tax_system);
                break;
            case KKMTypes::KKM_ACTION_REFUND:
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
                (float)$value["discount"],
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

        $INN = $this->organizationDocument->getINN();

        // Добавляем чек в очередь
        try {
            $request = $this->QueueManager->putCheck($check);
            if (isset($res['state'])) {
                $this->getReport()->add(new KKMReport(), [
                    'type' => $this->name,
                    'typePayment' => $type,
                    'state' => KKMTypes::KKM_STATE_NEW,
                    'dataKKM' => $request,
                    'dataPost' => $data,
                    'action' => $data['action'],
                    'INN' => $INN
                ]);

                $this->sendMail($data, $type);
                return '';
            } else {
                $this->getReport()->add(new KKMReport(), [
                    'type' => $this->name,
                    'typePayment' => $type,
                    'state' => KKMTypes::KKM_STATE_OTHER_ERROR,
                    'dataKKM' => $request,
                    'action' => KKMTypes::KKM_ACTION_ERROR,
                    'INN' => $INN
                ]);
            }
        } catch (\Exception $error) {
            $this->getReport()->add(new KKMReport(), [
                'type' => $this->name,
                'typePayment' => $type,
                'state' => KKMTypes::KKM_STATE_ERROR,
                'dataKKM' => ["error_description" => $error->getMessage()],
                'action' => KKMTypes::KKM_ACTION_ERROR,
                'INN' => $INN
            ]);
            return $error->getMessage();
        }

        return KKMTypes::KKM_STATE_OTHER_ERROR;
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
        if (!is_null($this->QueueManager)) {
            return $this->QueueManager->isQueueActive($name);
        } else {
            return false;
        }
	}

    /**
     * @return false
     */
    public function checkKKM(): bool
    {
        $komtet = $this->kkmDocument->getData();
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