<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Cashbox\BoxBundle\Model\Type\{KKMTypes, OtherTypes, PaymentTypes};
use Doctrine\MongoDB\Database;

class Version20191201111548 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return "Repair type payment of ReportKKM";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('ReportKKM');
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
                $newdata = [
                    '$set' => [
                        "type" => KKMTypes::KKM_TYPE_KOMTET,
                        "typePayment" => $value
                    ]
                ];
                $collection->update(['_id' => $document['_id']], $newdata);
            }
        }
    }

    public function down(Database $db)
    {
    }
}