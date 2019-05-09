<?php

namespace Cashbox\BoxBundle\Model\Report;

use Cashbox\BoxBundle\Document\SberbankTransaction;

class SberbankReport extends ReportAbstract implements ReportInterface
{
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