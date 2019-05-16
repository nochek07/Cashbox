<?php

namespace Cashbox\BoxBundle\Model\Report;

use Cashbox\BoxBundle\Document\YandexTransaction;

/**
 * Class YandexReport
 * @package Cashbox\BoxBundle\Model\Report
 */
class YandexReport extends ReportAbstract implements ReportInterface
{
    /**
     * @param array $params
     * @return void
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