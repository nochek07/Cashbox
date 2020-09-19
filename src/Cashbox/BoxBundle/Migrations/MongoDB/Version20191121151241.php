<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use MongoDB\Database;

class Version20191121151241 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return "Repair TIN of transactions and report";
    }

    public function up(Database $db)
    {
        $params = [
            'type' => 'string',
            'oldName' => 'inn',
            'newName' => 'tin'
        ];
        $this->repairTin($db, 'ReportKKM', $params);
        $this->repairTin($db, 'SberbankTransaction', $params);
        $this->repairTin($db, 'YandexTransaction', $params);
    }

    public function down(Database $db)
    {
        $params = [
            'type' => 'int',
            'oldName' => 'tin',
            'newName' => 'inn'
        ];
        $this->repairTin($db, 'ReportKKM', $params);
        $this->repairTin($db, 'SberbankTransaction', $params);
        $this->repairTin($db, 'YandexTransaction', $params);
    }

    /**
     * Repair type of TIN
     *
     * @param Database $db
     * @param string $tableName
     * @param array $params
     */
    private function repairTin(Database $db, string $tableName, array $params)
    {
        $collection = $db->selectCollection($tableName);
        $cursor = $collection->find();
        $it = new \IteratorIterator($cursor);
        $it->rewind();
        while ($document = $it->current()) {
            $oldName = $params['oldName'];
            if (isset($document[$oldName])) {
                $value = $document[$oldName];
                settype($value, $params['type']);

                $collection->updateOne(['_id' => $document['_id']], ['$set' => [$params['newName'] => $value]]);
                $collection->updateOne(['_id' => $document['_id']], ['$unset' => [$oldName => true]]);
            }
            $it->next();
        }
    }
}
