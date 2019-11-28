<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Cashbox\BoxBundle\Model\Type\PaymentTypes;
use Doctrine\MongoDB\Database;

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
        while ($document = $list->getNext()) {
            $newdata = $document;
            $newdata['type'] = PaymentTypes::PAYMENT_TYPE_YANDEX;
            unset($newdata['_id']);

            $collection->insert($newdata);
        }
    }

    public function down(Database $db)
    {
    }
}