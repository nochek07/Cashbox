<?php

namespace Cashbox\BoxBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use MongoDB\Database;
use Symfony\Component\DependencyInjection\{ContainerAwareInterface, ContainerInterface};

class Version20191128202642 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getDescription(): string
    {
        return "Rename collections";
    }

    public function up(Database $db)
    {
        $manager = $this->container->get("doctrine_mongodb.odm.document_manager");
        $connection = $manager->getClient();
        $dbName = $db->getDatabaseName();
        $connection->selectDatabase($dbName);

        $connection->admin->command([
            "renameCollection" => "{$dbName}.SberbankTransaction",
            "to" => "{$dbName}.transactions"
        ]);
        $connection->admin->command([
            "renameCollection" => "{$dbName}.ReportKomtet",
            "to" => "{$dbName}.tillReports"
        ]);
        $connection->admin->command([
            "renameCollection" => "{$dbName}.Organization",
            "to" => "{$dbName}.organizations"
        ]);
        $connection->admin->command([
            "renameCollection" => "{$dbName}.User",
            "to" => "{$dbName}.users"
        ]);
    }

    public function down(Database $db)
    {
    }
}