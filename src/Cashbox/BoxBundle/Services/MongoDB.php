<?php

namespace Cashbox\BoxBundle\Services;

use Cashbox\BoxBundle\Document\SberbankTransaction;
use Cashbox\BoxBundle\Document\YandexTransaction;
use Cashbox\BoxBundle\Document\ReportKomtet;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class MongoDB
{
    CONST ERROR_FROM_SITE = "yandex";
    CONST ERROR_FROM_1C   = "1c";
    CONST ERROR_FROM_SBER = "sberbank";

    /**
     * @var ManagerRegistry $doctrine_mongodb
     */
    protected $doctrine_mongodb = null;

    /**
     * MongoDB constructor.
     * @param ManagerRegistry $doctrine_mongodb
     */
    public function __construct(ManagerRegistry $doctrine_mongodb)
    {
        $this->doctrine_mongodb = $doctrine_mongodb;
    }

    /**
     * Запись транзакции в БД (Яндекс)
     *
     * @param array $params
     */
    public function setYandexTransaction(array $params)
    {
        $transaction = new YandexTransaction();
        $transaction->setDatetime();
        $transaction->setAction($params['action']);
        $transaction->setSum($params['orderSum']);
        $transaction->setCustomerNumber($params['customerNumber']);
        $transaction->setEmail($params['email']);
        $transaction->setDataPost($params['data']);
        $transaction->setInn($params['inn']);

        $dm = $this->doctrine_mongodb->getManager();
        $dm->persist($transaction);
        $dm->flush();
    }

    /**
     * Запись транзакции в БД (Сбербанк)
     *
     * @param array $params
     */
    public function setSberbankTransaction(array $params)
    {
        $transaction = new SberbankTransaction();
        $transaction->setDatetime();
        $transaction->setSum($params['orderSum']);
        $transaction->setCustomerNumber($params['customerNumber']);
        $transaction->setEmail($params['email']);
        $transaction->setDataPost($params['data']);
        $transaction->setInn($params['inn']);

        $dm = $this->doctrine_mongodb->getManager();
        $dm->persist($transaction);
        $dm->flush();
    }

    /**
     * Запись в БД (Комтет)
     *
     * @param array $params
     */
    public function setReportKomtet(array $params)
    {
        $Report = new ReportKomtet();
        $Report->setDatetime();
        $Report->setType($params['type']);
        $Report->setState($params['state']);
        $Report->setInn($params['inn']);

        if(isset($params['dataKomtet'])) {
            $Report->setDataKomtet($params['dataKomtet']);
        } else {
            $Report->setDataKomtet([]);
        }

        if(isset($params['dataPost'])) {
            $Report->setDataPost($params['dataPost']);

            if (isset($dataPost["uuid"]))
                $Report->setUuid($params['dataPost']["uuid"]);

            if (isset($dataPost["action"]))
                $Report->setAction($params['dataPost']["action"]);
        } else {
            $Report->setDataPost([]);
        }

        $dm = $this->doctrine_mongodb->getManager();
        $dm->persist($Report);
        $dm->flush();
    }
}