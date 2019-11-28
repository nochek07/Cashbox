<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Doctrine\MongoDB\Database;

class Version20191126211359 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
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