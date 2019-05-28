<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\OrganizationModel;
use Cashbox\BoxBundle\Model\KKM\Komtet;
use Cashbox\BoxBundle\Model\Payment\YandexPayment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{Request, Response};

class YandexController extends Controller
{
    /**
     * Отправка чека
     *
     * @Route("/aviso", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function avisoAction(Request $request)
    {
        $responseText = '';
        if ($request->isMethod(Request::METHOD_POST)) {
            $manager = $this->get('doctrine_mongodb');
            /**
             * @var Organization $Organization
             */
            $Organization = OrganizationModel::getOrganization($request, $manager);
            if (!is_null($Organization)) {
                $KKM = new Komtet($Organization, $manager);
                $KKM->setMailer($this->get('cashbox.mailer'));

                $YandexPayment = new YandexPayment($manager);
                $responseText = $YandexPayment->send($request, $Organization, $KKM);
            }
        }

        return new Response($responseText);
    }

    /**
     * Проверка
     *
     * @Route("/check", schemes={"https"})
     * @param  Request $request
     * @return Response
     */
    public function checkAction(Request $request)
    {
        $responseText = '';
        if ($request->isMethod(Request::METHOD_POST)) {
            $manager = $this->get('doctrine_mongodb');
            /**
             * @var Organization $Organization
             */
            $Organization = OrganizationModel::getOrganization($request, $manager);
            if (!is_null($Organization)) {
                $KKM = new Komtet($Organization, $manager);

                $YandexPayment = new YandexPayment($manager);
                $responseText = $YandexPayment->check($request, $Organization, $KKM);
            }
        }

        return new Response($responseText);
    }
}