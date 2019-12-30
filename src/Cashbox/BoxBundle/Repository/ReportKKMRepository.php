<?php

namespace Cashbox\BoxBundle\Repository;

use Doctrine\MongoDB\Query\Expr;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class ReportKKMRepository extends DocumentRepository
{
    /**
     * @param \DateTime $datePeriodStart
     * @param \DateTime $datePeriodEnd
     * @return array
     */
    public function findByPeriod($datePeriodStart, $datePeriodEnd)
    {
        $Expr1_1 = new Expr();
        $Expr1_1->field('datetime')
            ->gte($datePeriodStart);

        $Expr1_2 = new Expr();
        $Expr1_2->field('datetime')
            ->lte($datePeriodEnd);

        $ExprAnd = new Expr();
        $ExprAnd->addAnd($Expr1_1)
            ->addAnd($Expr1_2);

        $query = $this->createQueryBuilder()
            ->field('state')->equals('new')
            ->addAnd($ExprAnd)
            ->getQuery()
        ;

        return $query->toArray();
    }
}
