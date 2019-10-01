<?php


namespace Cashbox\BoxBundle\Service;

use Cashbox\BoxBundle\Model\Report\ReportInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class Report
{
    /**
     * @var ManagerRegistry
     */
    private $manager;

    /**
     * Report constructor.
     *
     * @param ManagerRegistry $manager
     */
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Add report
     *
     * @param ReportInterface $report
     * @param array $param
     */
    public function add(ReportInterface $report, array $param)
    {
        $dm = $this->manager->getManager();
        $dm->persist($report->create($param));
        $dm->flush();
    }
}