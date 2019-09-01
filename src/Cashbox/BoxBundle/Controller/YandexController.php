<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\DependencyInjection\Box;
use Cashbox\BoxBundle\Model\Payment\YandexPayment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};

class YandexController extends AbstractController
{
    /**
     * Отправка чека
     *
     * @Route("/aviso", schemes={"https"})
     * @param Request $request
     * @param Box $box
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
     * Проверка
     *
     * @Route("/check", schemes={"https"})
     * @param Request $request
     * @param Box $box
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