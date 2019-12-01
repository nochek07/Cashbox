<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Doctrine\MongoDB\Database;

class Version20191201123422 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return "Repair ReportKKM";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('ReportKKM');
        $list = $collection->find();
        while ($document = $list->getNext()) {
            if (isset($document['action'])) {
                $newdata = [
                    '$set' => [
                        "dataKKM" => $document['dataKomtet']
                    ]
                ];
                $collection->update(['_id' => $document['_id']], $newdata);
                $collection->update(['_id' => $document['_id']], [
                    '$unset' => [
                        'action' => true,
                        'dataKomtet' => true
                    ]
                ]);
            }
        }
    }

    public function down(Database $db)
    {
    }
}