<?php

namespace Cashbox\BoxBundle\Model\Report;

use Cashbox\BoxBundle\Document\ReportKKM;

class KKMReport implements ReportInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $params)
    {
        $Report = new ReportKKM();
        $Report->setDatetime();
        $Report->setType($params['type']);
        $Report->setState($params['state']);
        $Report->setTypePayment($params['typePayment']);
        $Report->setInn($params['inn']);

        if (isset($params['dataKKM'])) {
            $Report->setDataKomtet($params['dataKKM']);
        } else {
            $Report->setDataKomtet([]);
        }

        if (isset($params['dataPost'])) {
            $Report->setDataPost($params['dataPost']);

            if (isset($dataPost["uuid"])) {
                $Report->setUuid($params['dataPost']["uuid"]);
            }
        } else {
            $Report->setDataPost([]);
        }

        return $Report;
    }
}