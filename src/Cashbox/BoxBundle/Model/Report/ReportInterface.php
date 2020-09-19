<?php

namespace Cashbox\BoxBundle\Model\Report;

interface ReportInterface
{
    /**
     * Create document
     *
     * @param array $params
     *
     * @return mixed
     */
    public function create(array $params);
}
