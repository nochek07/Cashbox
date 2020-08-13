<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use MongoDB\Database;

class Version20191128202642 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return "Rename SberbankTransaction collection";
    }

    public function up(Database $db)
    {
        $dbName = $db->getName();
        $mongo = $db->getConnection()->getMongoClient();
        $mongo->admin->command([
            "renameCollection" => "{$dbName}.SberbankTransaction",
            "to" => "{$dbName}.Transaction"
        ]);
    }

    public function down(Database $db)
    {
    }
}