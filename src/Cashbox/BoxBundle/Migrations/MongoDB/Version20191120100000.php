<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use MongoDB\Database;

class Version20191120100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Repair TIN to ReportKomtet";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('ReportKomtet');
        $cursor = $collection->find();
        $it = new \IteratorIterator($cursor);
        $it->rewind();
        while ($document = $it->current()) {
            if (isset($document['inn'])) {
                $value = $document['inn'];
                settype($value, 'string');
                $collection->updateOne(['_id' => $document['_id']], ['$set' => ["tin" => $value]]);
                $collection->updateOne(['_id' => $document['_id']], ['$unset' => ['inn' => true]]);
            }
            $it->next();
        }
    }

    public function down(Database $db)
    {
    }
}