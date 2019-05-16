<?php

namespace Cashbox\BoxBundle\Model\Report;

use Cashbox\BoxBundle\Document\SberbankTransaction;

/**
 * Class SberbankReport
 * @package Cashbox\BoxBundle\Model\Report
 */
class SberbankReport extends ReportAbstract implements ReportInterface
{
    /**
     * @param array $params
     * @return void
     */
    public function create(array $params)
    {
        $transaction = new SberbankTransaction();
        $transaction->setDatetime();
        $transaction->setSum($params['orderSum']);
        $transaction->setCustomerNumber($params['customerNumber']);
        $transaction->setEmail($params['email']);
        $transaction->setDataPost($params['data']);
        $transaction->setInn($params['inn']);

        $dm = $this->manager->getManager();
        $dm->persist($transaction);
        $dm->flush();
    }
}