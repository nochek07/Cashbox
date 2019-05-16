<?php

namespace Cashbox\BoxBundle\Model\Report;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * Class ReportAbstract
 * @package Cashbox\BoxBundle\Model\Report
 */
abstract class ReportAbstract
{
    /**
     * @var ManagerRegistry $manager
     */
    protected $manager = null;

    /**
     * ReportAbstract constructor.
     *
     * @param ManagerRegistry $manager
     */
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }
}