<?php

namespace Cashbox\BoxBundle\Model\Report;

use Cashbox\BoxBundle\Document\YandexTransaction;

class YandexReport extends ReportAbstract implements ReportInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $params)
    {
        $transaction = new YandexTransaction();
        $transaction->setDatetime();
        $transaction->setAction($params['action']);
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