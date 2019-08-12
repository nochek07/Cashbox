<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\Payment\SberbankPayment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\{Request, Response};

class SberbankController extends PaymentController
{
    /**
     * Создание заказа и отправка настраницу оплаты в случае успеха
     *
     * @Route("/restSberbank", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function restSberbankAction(Request $request)
    {
        $SberbankPayment = new SberbankPayment($this->get('doctrine_mongodb'));
        $url = $SberbankPayment->getSiteUrl($request, 0);

        if ($request->isMethod(Request::METHOD_GET)) {
            $this->setOrganization($request);
            $Organization = $this->getOrganization();
            if ($Organization instanceof Organization) {
                $url = $SberbankPayment->getRedirectUrl(
                    $request, $Organization, $url, $this->getKKM()
                );
            }
        }
        return $this->redirect($url);
    }

    /**
     * Отправка чека по callback
     *
     * @Route("/callbackSberbank", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function callbackSberbankAction(Request $request)
    {
        if ($request->isMethod(Request::METHOD_GET)) {
            $this->send($request, new SberbankPayment($this->get('doctrine_mongodb')));
        }
        return new Response('');
    }
}