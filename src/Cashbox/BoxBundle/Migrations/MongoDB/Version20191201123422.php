<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use MongoDB\Database;

class Version20191201123422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Repair tillReports";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('tillReports');
        $cursor = $collection->find();
        $it = new \IteratorIterator($cursor);
        $it->rewind();
        while ($document = $it->current()) {
            if (isset($document['dataKomtet'])) {
                $collection->updateOne(['_id' => $document['_id']], ['$set' => ["dataTill" => $document['dataKomtet']]]);
                $collection->updateOne(['_id' => $document['_id']], ['$unset' => ['dataKomtet' => true]]);
            }
            $it->next();
        }
    }

    public function down(Database $db)
    {
    }
}