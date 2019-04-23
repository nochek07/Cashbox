<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Model\Payment\SberbankPayment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SberbankController extends Controller
{
    /**
     * @Route("/restSberbank", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function restSberbankAction(Request $request) {
        $SberbankPayment = new SberbankPayment($this->get('service_container'));
        $url = $SberbankPayment->rest($request);

        return $this->redirect($url);
    }

    /**
     * @Route("/callbackSberbank", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function callbackSberbankAction(Request $request)
    {
        $SberbankPayment = new SberbankPayment($this->get('service_container'));
        $SberbankPayment->send($request);

        return new Response('');
    }
}