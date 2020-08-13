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
        return "Repair INN of transactions and report";
    }

    public function up(Database $db)
    {
        $params = [
            'type' => 'string',
            'oldName' => 'inn',
            'newName' => 'INN'
        ];
        $this->repairINN($db, 'ReportKKM', $params);
        $this->repairINN($db, 'SberbankTransaction', $params);
        $this->repairINN($db, 'YandexTransaction', $params);
    }

    public function down(Database $db)
    {
        $params = [
            'type' => 'int',
            'oldName' => 'INN',
            'newName' => 'inn'
        ];
        $this->repairINN($db, 'ReportKKM', $params);
        $this->repairINN($db, 'SberbankTransaction', $params);
        $this->repairINN($db, 'YandexTransaction', $params);
    }

    /**
     * Repair type of INN
     *
     * @param Database $db
     * @param string $tableName
     * @param array $params
     */
    private function repairINN(Database $db, string $tableName, array $params)
    {
        $collection = $db->selectCollection($tableName);
        $list = $collection->find();
        while ($document = $list->getNext()) {
            $oldName = $params['oldName'];
            if (isset($document[$oldName])) {
                $value = $document[$oldName];
                settype($value, $params['type']);

                $collection->update(['_id' => $document['_id']], ['$set' => [$params['newName'] => $value]]);
                $collection->update(['_id' => $document['_id']], ['$unset' => [$oldName => true]]);
            }
        }
    }
}
