<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Model\Payment\YandexPayment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class YandexController extends Controller
{
    /**
     * @Route("/aviso", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function avisoAction(Request $request)
    {
        $YandexPayment = new YandexPayment($this->get('service_container'));
        $responseText = $YandexPayment->send($request);

        return new Response($responseText);
    }

    /**
     * @Route("/check", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function checkAction(Request $request)
    {
        $YandexPayment = new YandexPayment($this->get('service_container'));
        $responseText = $YandexPayment->check($request);

        return new Response($responseText);
    }
}