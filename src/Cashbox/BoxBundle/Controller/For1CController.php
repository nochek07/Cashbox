<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Service\Box;
use Cashbox\BoxBundle\Model\KKM\KKMMessages;
use Cashbox\BoxBundle\Model\Payment\For1CPayment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};

class For1CController extends AbstractController
{
    /**
     * Отправка чека из 1С
     *
     * @Route("/send1c", schemes={"https"})
     * @param Request $request
     * @param Box $box
     * @return Response
     */
    public function send1cAction(Request $request, Box $box)
    {
        $For1CPayment = new For1CPayment();
        if ($request->isMethod(Request::METHOD_POST)) {
            if ($request->getContentType() === 'json') {
                $data = json_decode($request->getContent(), true);
                if (!is_null($data)) {
                    $For1CPayment->setDataJSON($data);

                    $box->setOrganizationTextError(
                        $For1CPayment->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_ERROR_INN)
                    );
                    return new Response(
                        $box->send($request, $For1CPayment)
                    );
                }
            }
        }

        return new Response(
            $For1CPayment->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_ERROR)
        );
    }

    /**
     * Проверка сайта/очереди из 1С
     *
     * @Route("/chek1c", schemes={"https"})
     * @param Request $request
     * @param Box $box
     * @return Response
     */
    public function chek1cAction(Request $request, Box $box)
    {
        $For1CPayment = new For1CPayment();
        if ($request->isMethod(Request::METHOD_POST)) {
            if ($request->getContentType() === 'json') {
                $data = json_decode($request->getContent(), true);
                if (!is_null($data)) {
                    $For1CPayment->setDataJSON($data);

                    $box->setOrganizationTextError(
                        $For1CPayment->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_ERROR_INN)
                    );
                    return new Response(
                        $box->check($request, $For1CPayment)
                    );
                }
            }
        }

        return new Response(
            $For1CPayment->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_ERROR)
        );
    }
}