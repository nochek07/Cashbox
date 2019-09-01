<?php

namespace Cashbox\BoxBundle\Model\Report;

use Cashbox\BoxBundle\Document\SberbankTransaction;

class SberbankReport implements ReportInterface
{
    /**
     * {@inheritDoc}
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

        return $transaction;
    }
}