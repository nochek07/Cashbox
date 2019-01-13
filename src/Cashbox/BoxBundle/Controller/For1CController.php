<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Models\Komtet;
use Cashbox\BoxBundle\Models\OrganizationModel;
use Cashbox\BoxBundle\Services\MongoDB;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class For1CController extends Controller
{
    /**
     * Отправка чека из 1С
     *
     * @Route("/send1c", schemes={"https"})
     * @param Request $request
     * @return Response
     */
    public function send1cAction(Request $request)
    {
        if($request->isMethod(Request::METHOD_POST)) {

            if($request->getContentType()==='json') {
                $postData = file_get_contents('php://input');
                $data     = json_decode($postData, true);
                if (!is_null($data)) {
                    /**
                     * @var Organization $Organization
                     */
                    $Organization = OrganizationModel::getOrganization($data, $this->get('doctrine_mongodb'));
                    if (!is_null($Organization)) {
                        $komtet = $Organization->getDataKomtet();

                        if ($this->check1cMD5($data, $Organization->getSecret())) {

                            $KomtetObj = new Komtet($Organization, $this->get('service_container'));

                            if (!$KomtetObj->isQueueActive($komtet['queue_name'])) {
                                return new Response(Komtet::buildResponse('For1C', 0, 100, null, Komtet::MSG_CASHBOX_UNAV));
                            } else {
                                $repository = $this->get("doctrine_mongodb")->getManager()
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
                                        return new Response(Komtet::buildResponse('For1C', 0, 0, null, null));
                                } else {
                                    $error = Komtet::MSG_ERROR_CHECK;
                                }

                                return new Response(Komtet::buildResponse('For1C', 0, 100, null, $error));
                            }
                        } else {
                            return new Response(Komtet::buildResponse('For1C', 0, 100, null, Komtet::MSG_ERROR_HASH));
                        }
                    } else {
                        return new Response(Komtet::buildResponse('For1C', 0, 100, null, Komtet::MSG_ERROR_INN));
                    }
                }
            }
        }

        return new Response(Komtet::buildResponse('For1C', 0, 100, null, Komtet::MSG_ERROR));
    }

    /**
     * Проверка сайта/очереди из 1С
     *
     * @Route("/chek1c", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function chek1cAction(Request $request)
    {
        if($request->isMethod(Request::METHOD_POST)) {
            if($request->getContentType()==='json') {
                $postData = file_get_contents('php://input');
                $data     = json_decode($postData, true);
                if (!is_null($data)) {
                    /**
                     * @var Organization $Organization
                     */
                    $Organization = OrganizationModel::getOrganization($data, $this->get('doctrine_mongodb'));
                    if (!is_null($Organization)) {
                        $KomtetObj = new Komtet($Organization, $this->get('service_container'));

                        $komtet = $Organization->getDataKomtet();
                        if (!$KomtetObj->isQueueActive($komtet['queue_name'])) {
                            return new Response(Komtet::buildResponse('For1C', 0, 100, null, Komtet::MSG_CASHBOX_UNAV));
                        } else {
                            return new Response(Komtet::buildResponse('For1C', 0, 0, null, null));
                        }
                    }
                }
            }
        }
        return new Response(Komtet::buildResponse('For1C', 0, 100, null, Komtet::MSG_ERROR));
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