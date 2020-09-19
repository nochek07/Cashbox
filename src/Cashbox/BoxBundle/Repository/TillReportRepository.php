<?php

namespace Cashbox\BoxBundle\Repository;

use Cashbox\BoxBundle\Repository\Form\ReportByPeriodFormType;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class TillReportRepository extends DocumentRepository
{
    /**
     * @param \DateTime $datePeriodStart
     * @param \DateTime $datePeriodEnd
     * @param string $tin
     *
     * @return array
     */
    public function findByPeriod(\DateTime $datePeriodStart, \DateTime $datePeriodEnd, string $tin): array
    {
        $query = $this->createQueryBuilder()
            ->field('state')
                ->equals('new')
            ->field('datetime')
                ->lte($datePeriodEnd)
            ->field('datetime')
                ->gte($datePeriodStart)
        ;

        if ($tin !== ReportByPeriodFormType::ALL_ORGANIZATION) {
            $query->field('tin')
                ->equals($tin)
            ;
        }

        return $query->getQuery()->toArray();
    }
}
