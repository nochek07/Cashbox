<?php

namespace Cashbox\BoxBundle\DependencyInjection;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\OrganizationModel;
use Cashbox\BoxBundle\Model\Payment\PaymentInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class Box
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
     * @var ManagerRegistry $manager
     */
    private $manager;

    /**
     * @var Report $report
     */
    private $report;

    /**
     * @var Mailer $mailer
     */
    private $mailer;

    /**
     * Box constructor.
     * 
     * @param ManagerRegistry $manager
     * @param Report $report
     * @param Mailer $mailer
     */
    public function __construct(ManagerRegistry $manager, Report $report, Mailer $mailer)
    {
        $this->manager = $manager;
        $this->report = $report;
        $this->mailer = $mailer;
    }

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
     * @param bool $isSend - send to the kkm
     * @return string
     */
    public function getResponseText(Request $request, PaymentInterface $payment, bool $isSend = true)
    {
        $this->defineOrganization($request);
        if ($this->getOrganization() instanceof Organization) {
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
        $this->setOptionsPayment($payment);
        if ($isSend) {
            return $payment->send($request);
        } else {
            return $payment->check($request);
        }
    }

    /**
     * @param PaymentInterface $payment
     */
    public function setOptionsPayment(PaymentInterface &$payment)
    {
        $payment->setOrganization($this->getOrganization());
        $payment->setReport($this->report);
        $payment->setManager($this->manager);
        $payment->setMailer($this->mailer);
    }

    /**
     * Define Organization
     *
     * @param Request $request
     */
    public function defineOrganization(Request $request)
    {
        $this->Organization = OrganizationModel::getOrganization(
            $request,
            $this->manager
        );
    }

    /**
     * @return Organization|null
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
}