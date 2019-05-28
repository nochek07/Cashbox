<?php

namespace Cashbox\BoxBundle\Model\Report;

use Cashbox\BoxBundle\Document\ReportKomtet;

class KomtetReport extends ReportAbstract implements ReportInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $params)
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

        $dm = $this->manager->getManager();
        $dm->persist($Report);
        $dm->flush();
    }
}