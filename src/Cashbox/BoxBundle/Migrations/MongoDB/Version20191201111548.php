<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Cashbox\BoxBundle\Model\Type\{KKMTypes, OtherTypes, PaymentTypes};
use MongoDB\Database;

class Version20191201111548 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return "Repair type payment of ReportKomtet";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('ReportKomtet');
        $list = $collection->find();
        while ($document = $list->getNext()) {
            if (!isset($document['typePayment'])) {
                switch ($document["type"]) {
                    case "1c":
                        $value = OtherTypes::PAYMENT_TYPE_1C;
                        break;
                    case "sberbank":
                        $value = PaymentTypes::PAYMENT_TYPE_SBERBANK;
                        break;
                    case "yandex":
                        $value = PaymentTypes::PAYMENT_TYPE_YANDEX;
                        break;
                    default:
                        $value = "";
                }
                $newData = [
                    '$set' => [
                        "type" => KKMTypes::KKM_TYPE_KOMTET,
                        "typePayment" => $value
                    ]
                ];
                $collection->update(['_id' => $document['_id']], $newData);
            }
        }
    }

    public function down(Database $db)
    {
    }
}