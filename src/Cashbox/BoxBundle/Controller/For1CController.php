<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Model\KKM\KKMMessages;
use Cashbox\BoxBundle\Model\Payment\For1CPayment;
use Cashbox\BoxBundle\Service\Box;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Annotation\Route;

class For1CController extends AbstractController
{
    /**
     * Sending a check from 1C
     *
     * @Route("/send1c", schemes={"https"})
     *
     * @param Request $request
     * @param Box $box
     *
     * @return Response
     */
    public function send1cAction(Request $request, Box $box)
    {
        $for1CPayment = new For1CPayment();
        if ($request->isMethod(Request::METHOD_POST)) {
            if ($request->getContentType() === 'json') {
                $data = json_decode($request->getContent(), true);
                if (!is_null($data) && is_array($data)) {
                    $for1CPayment->setDataJSON($data);

                    $box->setOrganizationTextError(
                        $for1CPayment->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_ERROR_INN)
                    );
                    return new Response(
                        $box->send($request, $for1CPayment)
                    );
                }
            }
        }

        return new Response(
            $for1CPayment->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_ERROR)
        );
    }

    /**
     * Checking a site/queue from 1C
     *
     * @Route("/chek1c", schemes={"https"})
     *
     * @param Request $request
     * @param Box $box
     *
     * @return Response
     */
    public function chek1cAction(Request $request, Box $box)
    {
        $for1CPayment = new For1CPayment();
        if ($request->isMethod(Request::METHOD_POST)) {
            if ($request->getContentType() === 'json') {
                $data = json_decode($request->getContent(), true);
                if (!is_null($data)) {
                    $for1CPayment->setDataJSON($data);

                    $box->setOrganizationTextError(
                        $for1CPayment->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_ERROR_INN)
                    );
                    return new Response(
                        $box->check($request, $for1CPayment)
                    );
                }
            }
        }

        return new Response(
            $for1CPayment->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_ERROR)
        );
    }
}