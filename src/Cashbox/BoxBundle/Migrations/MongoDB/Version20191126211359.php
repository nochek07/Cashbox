<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use MongoDB\Database;

class Version20191126211359 extends AbstractMigration
{
    public function getDescription(): string 
    {
        return "Drop YandexTransaction";
    }

    public function up(Database $db)
    {
        $collection = $db->selectCollection('YandexTransaction');
        $collection->drop();
    }

    public function down(Database $db)
    {
    }
}