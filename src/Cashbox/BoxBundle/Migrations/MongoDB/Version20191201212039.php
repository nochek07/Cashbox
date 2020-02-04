<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Doctrine\MongoDB\Database;

class Version20191201212039 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return "Rename ReportKKM collection";
    }

    public function up(Database $db)
    {
        $dbName = $db->getName();
        $mongo = $db->getConnection()->getMongoClient();
        $mongo->admin->command([
            "renameCollection" => "{$dbName}.ReportKomtet",
            "to" => "{$dbName}.ReportKKM"
        ]);
    }

    public function down(Database $db)
    {
    }
}