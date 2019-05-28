<?php

namespace Cashbox\BoxBundle\Model\Report;

interface ReportInterface
{
    /**
     * Добавление записи
     *
     * @param array $params
     * @return mixed
     */
    public function create(array $params);
}
