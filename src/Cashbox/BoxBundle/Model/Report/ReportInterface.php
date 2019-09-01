<?php

namespace Cashbox\BoxBundle\Model\Report;

interface ReportInterface
{
    /**
     * Добавление данных
     *
     * @param array $params
     * @return ReportInterface
     */
    public function create(array $params);
}
