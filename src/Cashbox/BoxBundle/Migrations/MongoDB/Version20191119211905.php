<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use MongoDB\Database;

class Version20191119211905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Repair type of TIN to Organization";
    }

    public function up(Database $db)
    {
        $this->repairTin($db, 'string');
    }

    public function down(Database $db)
    {
    }

    /**
     * Repair type of TIN
     *
     * @param Database $db
     * @param string $type
     */
    private function repairTin(Database $db, string $type)
    {
        $collection = $db->selectCollection('Organization');
        $cursor = $collection->find();
        $it = new \IteratorIterator($cursor);
        $it->rewind();
        while ($document = $it->current()) {
            if (isset($document['INN'])) {
                $value = $document['INN'];
                settype($value, $type);
                $collection->updateOne(['_id' => $document['_id']], ['$set' => ["tin" => $value]]);
                $collection->updateOne(['_id' => $document['_id']], ['$unset' => ['INN' => true]]);
            }
            $it->next();
        }
    }
}