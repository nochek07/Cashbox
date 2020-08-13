<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use MongoDB\Database;

class Version20191119211905 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return "Repair type of INN to Organization";
    }

    public function up(Database $db)
    {
        $this->repairINN($db, 'string');
    }

    public function down(Database $db)
    {
        $this->repairINN($db, 'int');
    }

    /**
     * Repair type of INN
     *
     * @param Database $db
     * @param string $type
     */
    private function repairINN(Database $db, string $type)
    {
        $collection = $db->selectCollection('Organization');
        $list = $collection->find();
        while ($document = $list->getNext()) {
            if (isset($document['INN'])) {
                $value = $document['INN'];
                settype($value, $type);
                $collection->update(['_id' => $document['_id']], ['$set' => ["INN" => $value]]);
            }
        }
    }
}