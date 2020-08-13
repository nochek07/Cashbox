<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use MongoDB\Database;

class Version20191201123422 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return "Repair ReportKomtet";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('ReportKomtet');
        $list = $collection->find();
        while ($document = $list->getNext()) {
            if (isset($document['dataKomtet'])) {
                $collection->update(['_id' => $document['_id']], ['$set' => ["dataKKM" => $document['dataKomtet']]]);
                $collection->update(['_id' => $document['_id']], ['$unset' => ['dataKomtet' => true]]);
            }
        }
    }

    public function down(Database $db)
    {
    }
}