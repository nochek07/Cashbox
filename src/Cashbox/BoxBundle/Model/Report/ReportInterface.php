<?php

namespace Cashbox\BoxBundle\Model\Report;

/**
 * Interface ReportInterface
 * @package Cashbox\BoxBundle\Model\Report
 */
interface ReportInterface
{
    /**
     * @param array $params
     * @return mixed
     */
    public function create(array $params);
}
