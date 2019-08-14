<?php

namespace Cashbox\BoxBundle\Controller;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\KKM\{KKMInterface, Komtet};
use Cashbox\BoxBundle\Model\OrganizationModel;
use Cashbox\BoxBundle\Model\Payment\PaymentInterface;;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends Controller
{
    /**
     * @var Organization|null $Organization;
     */
    private $Organization;

    /**
     * @var string $OrganizationTextError;
     */
    private $OrganizationTextError = '';

    /**
     * Проверка
     *
     * @param Request $request
     * @param PaymentInterface $payment
     * @return string
     */
    public function check(Request $request, PaymentInterface $payment)
    {
        return $this->getResponseText($request, $payment, false);
    }

    /**
     * Отправка
     *
     * @param Request $request
     * @param PaymentInterface $payment
     * @return string
     */
    public function send(Request $request, PaymentInterface $payment)
    {
        return $this->getResponseText($request, $payment, true);
    }

    /**
     * Get Response Text
     *
     * @param Request $request
     * @param PaymentInterface $payment
     * @param bool $isSend
     * @return string
     */
    public function getResponseText(Request $request, PaymentInterface $payment, bool $isSend = true)
    {
        $this->setOrganization($request);
        if ($this->Organization instanceof Organization) {
            return $this->getResponsePayment($request, $payment, $isSend);
        } else {
            return $this->OrganizationTextError;
        }
    }

    /**
     * Get Response Payment Send or Check
     *
     * @param Request $request
     * @param PaymentInterface $payment
     * @param bool $isSend
     * @return string
     */
    public function getResponsePayment(Request $request, PaymentInterface $payment, bool $isSend = true)
    {
        $KKM = $this->getKKM($isSend);
        if ($isSend) {
            return $payment->send($request, $this->Organization, $KKM);
        } else {
            return $payment->check($request, $this->Organization, $KKM);
        }
    }

    /**
     * Set Organization
     *
     * @param Request $request
     */
    public function setOrganization(Request $request)
    {
        $this->Organization = OrganizationModel::getOrganization($request, $this->get('doctrine_mongodb'));
    }

    /**
     * Get Organization
     */
    public function getOrganization()
    {
        return $this->Organization;
    }

    /**
     * Set Text Error for Organization
     *
     * @param $text
     */
    public function setOrganizationTextError($text)
    {
        $this->OrganizationTextError = $text;
    }

    /**
     * Get ККМ
     *
     * @param bool $mailer
     * @example true - for send, false - for check
     * @return KKMInterface
     */
    public function getKKM($mailer = false)
    {
        $KKM = new Komtet($this->Organization, $this->get('doctrine_mongodb'));
        if ($mailer) {
            $KKM->setMailer($this->get('cashbox.mailer'));
        }
        return $KKM;
    }
}