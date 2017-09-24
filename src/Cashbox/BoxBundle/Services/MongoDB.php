<?php

namespace Cashbox\BoxBundle\Services;

use Cashbox\BoxBundle\Document\YandexTransaction;
use Cashbox\BoxBundle\Document\ReportKomtet;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class MongoDB
{
    CONST ERROR_FROM_SITE = "yandex";
    CONST ERROR_FROM_1C   = "1c";

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
     * Запись транзакции в БД
     * @param $action - вид транзакции
     * @param $Sum - сумма
     * @param $email - email
     * @param $data - полные данные транзакции
     */
    public function setYandexTransaction($action, $Sum, $email, $data)
    {
        $transaction = new YandexTransaction();
        $transaction->setDatetime();
        $transaction->setAction($action);
        $transaction->setSum($Sum);
        $transaction->setEmail($email);
        $transaction->setDataPost($data);

        $dm = $this->doctrine_mongodb->getManager();
        $dm->persist($transaction);
        $dm->flush();
    }

    /**
     * Запись ошибки в БД
     * @param $type
     * @param $state
     * @param array $dataKomtet - Результирующий массив после фискализации
     * @param array $dataPost   - Массив входящих данных
     */
    public function setErrorSuccess($type, $state, $dataKomtet = array(), $dataPost = array())
    {
        $Report = new ReportKomtet();
        $Report->setDatetime();
        $Report->setType($type);
        $Report->setState($state);
        $Report->setDataKomtet($dataKomtet);
        $Report->setDataPost($dataPost);

        if(isset($dataPost["uuid"]))
            $Report->setUuid($dataPost["uuid"]);

        if(isset($dataPost["action"]))
            $Report->setAction($dataPost["action"]);

        $dm = $this->doctrine_mongodb->getManager();
        $dm->persist($Report);
        $dm->flush();
    }

    public function find1cReport($action, $uuid){
        $repository = $this->doctrine_mongodb
            ->getManager()
            ->getRepository('BoxBundle:ReportKomtet');

        return $repository->findOneBy(
            array(
                'type'   => self::ERROR_FROM_1C,
                'action' => $action,
                'uuid'   => $uuid
            )
        );
    }
}