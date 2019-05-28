<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\OrganizationModel;
use Cashbox\BoxBundle\Model\KKM\Komtet;
use Cashbox\BoxBundle\Model\Payment\SberbankPayment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{Request, Response};

class SberbankController extends Controller
{
    /**
     * Создание заказа и отправка настраницу оплаты в случае успеха
     *
     * @Route("/restSberbank", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function restSberbankAction(Request $request) {

        $manager = $this->get('doctrine_mongodb');

        $SberbankPayment = new SberbankPayment($manager);
        $url = $SberbankPayment->getSiteUrl($request, 0);

        if ($request->isMethod(Request::METHOD_GET)) {
            /**
             * @var Organization $Organization
             */
            $Organization = OrganizationModel::getOrganization($request, $manager);
            if (!is_null($Organization)) {
                $KKM = new Komtet($Organization, $manager);
                $url = $SberbankPayment->getRedirectUrl($request, $Organization, $url, $KKM);
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
            $manager = $this->get('doctrine_mongodb');
            /**
             * @var Organization $Organization
             */
            $Organization = OrganizationModel::getOrganization($request, $manager);
            if (!is_null($Organization)) {
                $KKM = new Komtet($Organization, $manager);
                $KKM->setMailer($this->get('cashbox.mailer'));

                $SberbankPayment = new SberbankPayment($manager);
                $SberbankPayment->send($request, $Organization, $KKM);
            }
        }

        return new Response('');
    }
}