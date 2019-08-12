<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Model\Payment\YandexPayment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\{Request, Response};

class YandexController extends PaymentController
{
    /**
     * Отправка чека
     *
     * @Route("/aviso", schemes={"https"})
     * @param Request $request
     * @return Response
     */
    public function avisoAction(Request $request)
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            return new Response(
                $this->send($request, new YandexPayment($this->get('doctrine_mongodb')))
            );
        } else {
            return new Response('');
        }
    }

    /**
     * Проверка
     *
     * @Route("/check", schemes={"https"})
     * @param Request $request
     * @return Response
     */
    public function checkAction(Request $request)
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            return new Response(
                $this->check($request, new YandexPayment($this->get('doctrine_mongodb')))
            );
        } else {
            return new Response('');
        }
    }
}