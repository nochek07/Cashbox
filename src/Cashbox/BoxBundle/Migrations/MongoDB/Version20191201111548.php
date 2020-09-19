<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Cashbox\BoxBundle\Model\Type\{OtherTypes, PaymentTypes, TillTypes};
use MongoDB\Database;

class Version20191201111548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Repair type payment of tillReports";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('tillReports');
        $cursor = $collection->find();
        $it = new \IteratorIterator($cursor);
        $it->rewind();
        while ($document = $it->current()) {
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
                        "type" => TillTypes::TILL_TYPE_KOMTET,
                        "typePayment" => $value
                    ]
                ];
                $collection->updateOne(['_id' => $document['_id']], $newData);
            }
            $it->next();
        }
    }

    public function down(Database $db)
    {
    }
}