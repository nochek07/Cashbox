<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Service\Box;
use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\Payment\SberbankPayment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};

class SberbankController extends AbstractController
{
    /**
     * Sending a check by callback
     *
     * @Route("/callbackSberbank", schemes={"https"})
     *
     * @param Request $request
     * @param Box $box
     *
     * @return Response
     */
    public function callbackSberbankAction(Request $request, Box $box)
    {
        if ($request->isMethod(Request::METHOD_GET)) {
            return new Response(
                $box->send($request, new SberbankPayment())
            );
        } else {
            return new Response('');
        }
    }

    /**
     * Creating an order and sending a payment page if successful
     *
     * @Route("/restSberbank", schemes={"https"})
     *
     * @param Request $request
     * @param Box $box
     *
     * @return Response
     */
    public function restSberbankAction(Request $request, $box)
    {
        $SberbankPayment = new SberbankPayment();
        $url = $SberbankPayment->getSiteUrl($request, 0);
        if ($request->isMethod(Request::METHOD_GET)) {
            $box->defineOrganization($request);
            if ($box->getOrganization() instanceof Organization) {
                $box->setOptionsPayment($SberbankPayment);
                $url = $SberbankPayment->getRedirectUrl($request, $url);
            }
        }
        return $this->redirect($url);
    }
}