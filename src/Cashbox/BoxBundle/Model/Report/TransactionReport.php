<?php

namespace Cashbox\BoxBundle\Model\Report;

use Cashbox\BoxBundle\Document\Transaction;

class TransactionReport implements ReportInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $params)
    {
        $transaction = new Transaction();
        $transaction->setDatetime();
        $transaction->setSum($params['orderSum']);
        $transaction->setCustomerNumber($params['customerNumber']);
        $transaction->setEmail($params['email']);
        $transaction->setDataPost($params['data']);
        $transaction->setInn($params['inn']);
        $transaction->setAction($params['action']);
        $transaction->setType($params['type']);

        return $transaction;
    }
}