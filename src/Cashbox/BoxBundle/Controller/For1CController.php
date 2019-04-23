<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Model\Payment\For1CPayment;
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
        $For1CPayment = new For1CPayment($this->get('service_container'));
        $responseText = $For1CPayment->send($request);

        return new Response($responseText);
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
        $For1CPayment = new For1CPayment($this->get('service_container'));
        $responseText = $For1CPayment->check($request);

        return new Response($responseText);
    }
}