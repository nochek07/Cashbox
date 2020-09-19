<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Cashbox\BoxBundle\Model\Type\TillTypes;
use MongoDB\Database;

class Version20200204172301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Repair Action to tillReports";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('tillReports');
        $cursor = $collection->find();
        $it = new \IteratorIterator($cursor);
        $it->rewind();
        while ($document = $it->current()) {
            if (!isset($document['action'])) {
                if ($document['state'] == TillTypes::TILL_STATE_ERROR || $document['state'] == TillTypes::TILL_STATE_OTHER_ERROR) {
                    $value = 'error';
                } else {
                    $value = TillTypes::TILL_ACTION_SALE;
                }
                $collection->updateOne(['_id' => $document['_id']], ['$set' => ["action" => $value]]);
            }
            $it->next();
        }
    }

    public function down(Database $db)
    {
    }
}