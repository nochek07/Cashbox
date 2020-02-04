<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Cashbox\BoxBundle\Model\Type\PaymentTypes;
use Doctrine\MongoDB\Database;

class Version20191126201940 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return "Repair Sberbank Transaction";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('SberbankTransaction');
        $list = $collection->find();
        while ($document = $list->getNext()) {
            if (!isset($document['type'])) {
                $newData = [
                    '$set' => [
                        'type' => PaymentTypes::PAYMENT_TYPE_SBERBANK,
                        'action' => 'send'
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