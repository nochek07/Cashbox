<?php

namespace Cashbox\BoxBundle\Repository;

use Cashbox\BoxBundle\Repository\Form\ReportByPeriodFormType;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class ReportKKMRepository extends DocumentRepository
{
    /**
     * @param \DateTime $datePeriodStart
     * @param \DateTime $datePeriodEnd
     * @param string $INN
     *
     * @return array
     */
    public function findByPeriod(\DateTime $datePeriodStart, \DateTime $datePeriodEnd, string $INN)
    {
        $query = $this->createQueryBuilder()
            ->field('state')
                ->equals('new')
            ->field('datetime')
                ->lte($datePeriodEnd)
            ->field('datetime')
                ->gte($datePeriodStart)
        ;

        if ($INN !== ReportByPeriodFormType::ALL_ORGANIZATION) {
            $query->field('INN')
                ->equals($INN)
            ;
        }

        return $query->getQuery()->toArray();
    }
}
