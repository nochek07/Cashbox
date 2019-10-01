<?php

namespace Cashbox\BoxBundle\Model\Report;

interface ReportInterface
{
    /**
     * Create data
     *
     * @param array $params
     *
     * @return ReportInterface
     */
    public function create(array $params);
}
