<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\Komtet;
use Cashbox\BoxBundle\Model\KomtetMessages;
use Cashbox\BoxBundle\Model\OrganizationModel;
use Cashbox\BoxBundle\Services\MongoDB;
use Symfony\Component\HttpFoundation\Request;

class For1CPayment extends YandexPayment
{
    /**
     * @param Request $request
     * @return string
     */
    public function send(Request $request)
    {
        if($request->isMethod(Request::METHOD_POST)) {
            if($request->getContentType()==='json') {
                $postData = file_get_contents('php://input');
                $data     = json_decode($postData, true);
                if (!is_null($data)) {
                    /**
                     * @var Organization $Organization
                     */
                    $Organization = OrganizationModel::getOrganization($data, $this->getContainer()->get('doctrine_mongodb'));
                    if (!is_null($Organization)) {
                        $komtet = $Organization->getDataKomtet();

                        if ($this->check1cMD5($data, $Organization->getSecret())) {

                            $KomtetObj = new Komtet($Organization, $this->getContainer());

                            if (!$KomtetObj->isQueueActive($komtet['queue_name'])) {
                                return $this->buildResponse('For1C', 0, 100, null, KomtetMessages::MSG_CASHBOX_UNAV);
                            } else {
                                $repository = $this->getContainer()->get("doctrine_mongodb")->getManager()
                                    ->getRepository('BoxBundle:ReportKomtet');
                                $report = $repository->findOneBy([
                                        'type'   => MongoDB::ERROR_FROM_1C,
                                        'action' => $data["action"],
                                        'uuid'   => $data["uuid"]
                                    ]
                                );

                                if (is_null($report)) {
                                    $error = $KomtetObj->sendKKM($data, MongoDB::ERROR_FROM_1C);
                                    if ($error === '')
                                        return $this->buildResponse('For1C', 0, 0, null, null);
                                } else {
                                    $error = KomtetMessages::MSG_ERROR_CHECK;
                                }

                                return $this->buildResponse('For1C', 0, 100, null, $error);
                            }
                        } else {
                            return $this->buildResponse('For1C', 0, 100, null, KomtetMessages::MSG_ERROR_HASH);
                        }
                    } else {
                        return $this->buildResponse('For1C', 0, 100, null, KomtetMessages::MSG_ERROR_INN);
                    }
                }
            }
        }

        return $this->buildResponse('For1C', 0, 100, null, KomtetMessages::MSG_ERROR);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function check(Request $request)
    {
        if($request->isMethod(Request::METHOD_POST)) {
            if($request->getContentType()==='json') {
                $postData = file_get_contents('php://input');
                $data     = json_decode($postData, true);
                if (!is_null($data)) {
                    /**
                     * @var Organization $Organization
                     */
                    $Organization = OrganizationModel::getOrganization($data, $this->getContainer()->get('doctrine_mongodb'));
                    if (!is_null($Organization)) {
                        $KomtetObj = new Komtet($Organization, $this->getContainer()->get('service_container'));

                        $komtet = $Organization->getDataKomtet();
                        if (!$KomtetObj->isQueueActive($komtet['queue_name'])) {
                            return $this->buildResponse('For1C', 0, 100, null, KomtetMessages::MSG_CASHBOX_UNAV);
                        } else {
                            return $this->buildResponse('For1C', 0, 0, null, null);
                        }
                    }
                }
            }
        }
        return $this->buildResponse('For1C', 0, 100, null, KomtetMessages::MSG_ERROR);
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