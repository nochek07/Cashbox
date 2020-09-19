<?php

namespace Cashbox\BoxBundle\Model\Report;

use Cashbox\BoxBundle\Document\TillReport;

class TillModelReport implements ReportInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $params)
    {
        $Report = new TillReport();
        $Report->setDatetime();
        $Report->setType($params['type']);
        $Report->setState($params['state']);
        $Report->setTypePayment($params['typePayment']);
        $Report->setTin($params['tin']);

        if (isset($params['dataTill'])) {
            $Report->setDataTill($params['dataTill']);
        }

        if (isset($params['dataPost'])) {
            $Report->setDataPost($params['dataPost']);

            if (isset($dataPost["uuid"])) {
                $Report->setUuid($params['dataPost']["uuid"]);
            }
        }

        return $Report;
    }
}