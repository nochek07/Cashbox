<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Cashbox\BoxBundle\Model\Type\PaymentTypes;
use MongoDB\Database;

class Version20191126204819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Transaction pooling";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('SberbankTransaction');

        $collectionYandex = $db->selectCollection('YandexTransaction');
        $cursor = $collectionYandex->find();
        $it = new \IteratorIterator($cursor);
        $it->rewind();

        if ($collectionYandex->countDocuments() > 0) {
            $transactions = [];
            while ($document = $it->current()) {
                $newData = $document;
                $newData['type'] = PaymentTypes::PAYMENT_TYPE_YANDEX;
                unset($newData['_id']);
                $transactions[] = $newData;
                $it->next();
            }
            $collection->insertMany($transactions);
        }
    }

    public function down(Database $db)
    {
    }
}