<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Model\Payment\YandexPayment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * Главная страница
     *
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->redirect(
            $this->getParameter('redirect_url')
        );
    }

    /**
     * @Route("/test", name="test")
     */
    public function testAction()
    {
//        $manager = $this->get('doctrine_mongodb')->getManager();
//
//        $organization = $manager->getRepository('BoxBundle:Organization')->find('5d542cf87d630672a060d0fe');
//
//        $yandex = new YandexPayment();
//        $yandex->setReport($this->get('cashbox.report'));
//        $yandex->setOrganization($organization);
//        $payment = $yandex->getDataPayment();
//        $data = $yandex->getKKM($payment);
//
//        echo '<pre>';
//        print_r($data);
//        echo '</pre>';

        return new Response('');
    }
}