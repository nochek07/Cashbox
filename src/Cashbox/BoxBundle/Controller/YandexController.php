<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Model\Payment\YandexPayment;
use Cashbox\BoxBundle\Service\Box;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Annotation\Route;

class YandexController extends AbstractController
{
    /**
     * Sending a receipt
     *
     * @Route("/aviso", schemes={"https"})
     *
     * @param Request $request
     * @param Box $box
     *
     * @return Response
     */
    public function avisoAction(Request $request, Box $box)
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            return new Response(
                $box->send($request, new YandexPayment())
            );
        } else {
            return new Response('');
        }
    }

    /**
     * Checking
     *
     * @Route("/check", schemes={"https"})
     *
     * @param Request $request
     * @param Box $box
     *
     * @return Response
     */
    public function checkAction(Request $request, Box $box)
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            return new Response(
                $box->check($request, new YandexPayment())
            );
        } else {
            return new Response('');
        }
    }
}