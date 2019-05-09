<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\KKM\{KKMInterface, KKMMessages};
use Symfony\Component\HttpFoundation\Request;

class For1CPayment extends YandexPayment
{
    private $dataJSON = [];

    /**
     * @param Request $request
     * @param Organization $Organization
     * @param KKMInterface|null $kkm
     * @return string
     */
    public function send(Request $request, Organization $Organization, $kkm = null)
    {
        $komtet = $Organization->getDataKomtet();
        if ($this->check1cMD5($this->dataJSON, $Organization->getSecret())) {
            if($kkm instanceof KKMInterface) {
                if($kkm->connect()){
                    if(!$kkm->isQueueActive($komtet['queue_name'])) {
                        return $this->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_CASHBOX_UNAV);
                    }
                } else {
                    return $this->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_CASHBOX_UNAV);
                }
            }

            $repository = $this->manager->getManager()
                ->getRepository('BoxBundle:ReportKomtet');
            $report = $repository->findOneBy([
                'type'   => PaymentTypes::PAYMENT_TYPE_1C,
                'action' => $this->dataJSON["action"],
                'uuid'   => $this->dataJSON["uuid"]
            ]);

            if (is_null($report) && $kkm instanceof KKMInterface) {
                $error = $kkm->send($this->dataJSON, PaymentTypes::PAYMENT_TYPE_1C);
                if ($error === '')
                    return $this->buildResponse('For1C', 0, 0, null, null);
            } else {
                $error = KKMMessages::MSG_ERROR_CHECK;
            }

            return $this->buildResponse('For1C', 0, 100, null, $error);
        } else {
            return $this->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_ERROR_HASH);
        }
    }

    /**
     * @param Request $request
     * @param Organization $Organization
     * @param KKMInterface|null $kkm
     * @return string
     */
    public function check(Request $request, Organization $Organization, $kkm = null)
    {
        $komtet = $Organization->getDataKomtet();
        if ($kkm instanceof KKMInterface) {
            if($kkm->connect()) {
                if(!$kkm->isQueueActive($komtet['queue_name'])) {
                    return $this->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_CASHBOX_UNAV);
                }
            } else {
                return $this->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_CASHBOX_UNAV);
            }
        }

        return $this->buildResponse('For1C', 0, 0, null, null);
    }

    /**
     * @param array $data
     * @return self
     */
    public function setDataJSON(array $data)
    {
        $this->dataJSON = $data;
        return $this;
    }

    /**
     * Checking the MD5 sign.
     *
     * @param  array $data payment parameters
     * @param  string $secret
     * @return bool true if MD5 hash is correct
     */
    private function check1cMD5(array $data, string $secret) {
        $hash = $data["action"].';'.$secret.';';
        if(isset($data["kkm"]["payment"]["card"])){
            $hash .= $data["kkm"]["payment"]["card"].';';
        }
        if(isset($data["kkm"]["payment"]["cash"])){
            $hash .= $data["kkm"]["payment"]["cash"].';';
        }
        $hash .= $data["order"].';'.$data["inn"].';';

        if(strtolower(md5($hash))==$data["hash"])
            return true;
        else
            return false;
    }
}