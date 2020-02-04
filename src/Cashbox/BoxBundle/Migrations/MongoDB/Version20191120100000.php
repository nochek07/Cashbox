<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Doctrine\MongoDB\Database;

class Version20191120100000 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return "Repair INN to ReportKomtet";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('ReportKomtet');
        $list = $collection->find();
        while ($document = $list->getNext()) {
            if (isset($document['inn'])) {
                $collection->update(['_id' => $document['_id']], ['$set' => ["INN" => $document['inn']]]);
                $collection->update(['_id' => $document['_id']], ['$unset' => ['inn' => true]]);
            }
        }
    }

    public function down(Database $db)
    {
    }
}