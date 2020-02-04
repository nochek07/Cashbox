<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Cashbox\BoxBundle\Model\Type\KKMTypes;
use Doctrine\MongoDB\Database;

class Version20200204172301 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return "Repair Action to ReportKKM";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('ReportKKM');
        $list = $collection->find();
        while ($document = $list->getNext()) {
            if (!isset($document['action'])) {
                if ($document['state'] == KKMTypes::KKM_STATE_ERROR || $document['state'] == KKMTypes::KKM_STATE_OTHER_ERROR) {
                    $value = 'error';
                } else {
                    $value = KKMTypes::KKM_ACTION_SALE;
                }
                $collection->update(['_id' => $document['_id']], ['$set' => ["action" => $value]]);
            }
        }
    }

    public function down(Database $db)
    {
    }
}