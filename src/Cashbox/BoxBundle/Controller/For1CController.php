<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Model\KKM\KKMMessages;
use Cashbox\BoxBundle\Model\Payment\For1CPayment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\{Request, Response};

class For1CController extends PaymentController
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
        $For1CPayment = new For1CPayment($this->get('doctrine_mongodb'));

        $responseText = null;
        if ($request->isMethod(Request::METHOD_POST)) {
            if ($request->getContentType() === 'json') {
                $postData = file_get_contents('php://input');
                $data = json_decode($postData, true);
                if (!is_null($data)) {
                    $For1CPayment->setDataJSON($data);

                    $this->setOrganizationTextError($For1CPayment->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_ERROR_INN));
                    $responseText = $this->send($request, $For1CPayment);
                }
            }
        }

        if (is_null($responseText)) {
            return new Response($For1CPayment->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_ERROR));
        } else {
            return new Response($responseText);
        }
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
        $For1CPayment = new For1CPayment($this->get('doctrine_mongodb'));

        $responseText = null;
        if ($request->isMethod(Request::METHOD_POST)) {
            if ($request->getContentType() === 'json') {
                $postData = file_get_contents('php://input');
                $data = json_decode($postData, true);
                if (!is_null($data)) {
                    $For1CPayment->setDataJSON($data);

                    $this->setOrganizationTextError($For1CPayment->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_ERROR_INN));
                    $responseText = $this->check($request, $For1CPayment);
                }
            }
        }

        if (is_null($responseText)) {
            return new Response($For1CPayment->buildResponse('For1C', 0, 100, null, KKMMessages::MSG_ERROR));
        } else {
            return new Response($responseText);
        }
    }
}