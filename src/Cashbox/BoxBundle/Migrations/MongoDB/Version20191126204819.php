<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Cashbox\BoxBundle\Model\Type\PaymentTypes;
use MongoDB\Database;

class Version20191126204819 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return "Transaction pooling";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('SberbankTransaction');

        $collectionYandex = $db->selectCollection('YandexTransaction');
        $list = $collectionYandex->find();
        if ($list->count()>0) {
            $transactions = [];
            while ($document = $list->getNext()) {
                $newData = $document;
                $newData['type'] = PaymentTypes::PAYMENT_TYPE_YANDEX;
                unset($newData['_id']);
                $transactions[] = $newData;
            }
            $collection->batchInsert($transactions);
        }
    }

    public function down(Database $db)
    {
    }
}