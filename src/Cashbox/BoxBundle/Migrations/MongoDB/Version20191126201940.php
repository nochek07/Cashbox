<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Cashbox\BoxBundle\Model\Type\PaymentTypes;
use MongoDB\Database;

class Version20191126201940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Repair Sberbank Transaction";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('SberbankTransaction');
        $cursor = $collection->find();
        $it = new \IteratorIterator($cursor);
        $it->rewind();
        while ($document = $it->current()) {
            if (!isset($document['type'])) {
                $newData = [
                    '$set' => [
                        'type' => PaymentTypes::PAYMENT_TYPE_SBERBANK,
                        'action' => 'send'
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