<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Services\Komtet;
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
				
				$komtet   = $this->getParameter('komtet');

                if(!is_null($data)) {
                    if($data["INN"]==$komtet['INN']) {
                        if ($this->check1cMD5($data)) {
                            $manager = $this->get("komtet.cashbox");

                            if (!$manager->isQueueActive($komtet['komtet_cashbox_name'])) {
                                return new Response(Komtet::buildResponse('For1C', 0, 100, null, Komtet::MSG_CASHBOX_UNAV));
                            } else {
                                $report = $this->get("mongodb.cashbox")
                                    ->find1cReport($data["action"], $data["uuid"]);
                                if(is_null($report)) {
                                    $error = $manager->sendKKM($data, MongoDB::ERROR_FROM_1C);
                                    if($error==='')
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
                $manager = $this->get("komtet.cashbox");

                $komtet = $this->getParameter('komtet');
                if (!$manager->isQueueActive($komtet['komtet_cashbox_name'])) {
                    return new Response(Komtet::buildResponse('For1C', 0, 100, null, Komtet::MSG_CASHBOX_UNAV));
                } else {
                    return new Response(Komtet::buildResponse('For1C', 0, 0, null, null));
                }
            }
        }

        return new Response(Komtet::buildResponse('For1C', 0, 100, null, Komtet::MSG_ERROR));
    }

    /**
     * Checking the MD5 sign.
     *
     * @param  array $data payment parameters
     * @return bool true if MD5 hash is correct
     */
    private function check1cMD5($data) {
        $hash = $data["action"].';'.$this->getParameter('handling_secret').';';
        if(isset($data["kkm"]["payment"]["card"])){
            $hash .= $data["kkm"]["payment"]["card"].';';
        }
        if(isset($data["kkm"]["payment"]["cash"])){
            $hash .= $data["kkm"]["payment"]["cash"].';';
        }
        $hash .= $data["order"].';'.$data["INN"].';';

        if(strtolower(md5($hash))==$data["hash"])
            return true;
        else
            return false;
    }
}