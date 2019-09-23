<?php

namespace Cashbox\BoxBundle\Service;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\OrganizationModel;
use Cashbox\BoxBundle\Model\Payment\PaymentAbstract;
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
     * @var KKMBuilder $kkmBuilder
     */
    private $kkmBuilder;

    /**
     * Box constructor.
     * 
     * @param ManagerRegistry $manager
     * @param Report $report
     * @param KKMBuilder $kkmBuilder
     */
    public function __construct(ManagerRegistry $manager, Report $report, KKMBuilder $kkmBuilder)
    {
        $this->manager = $manager;
        $this->report = $report;
        $this->kkmBuilder = $kkmBuilder;
    }

    /**
     * Проверка
     *
     * @param Request $request
     * @param PaymentAbstract $payment
     * @return string
     */
    public function check(Request $request, PaymentAbstract $payment)
    {
        return $this->getResponseText($request, $payment, false);
    }

    /**
     * Отправка
     *
     * @param Request $request
     * @param PaymentAbstract $payment
     * @return string
     */
    public function send(Request $request, PaymentAbstract $payment)
    {
        return $this->getResponseText($request, $payment, true);
    }

    /**
     * Get Response Text
     *
     * @param Request $request
     * @param PaymentAbstract $payment
     * @param bool $isSend - send to the kkm
     * @return string
     */
    public function getResponseText(Request $request, PaymentAbstract $payment, bool $isSend = true)
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
     * @param PaymentAbstract $payment
     * @param bool $isSend
     * @return string
     */
    public function getResponsePayment(Request $request, PaymentAbstract $payment, bool $isSend = true)
    {
        $this->setOptionsPayment($payment);
        if ($isSend) {
            return $payment->send($request);
        } else {
            return $payment->check($request);
        }
    }

    /**
     * @param PaymentAbstract $payment
     */
    public function setOptionsPayment(PaymentAbstract &$payment)
    {
        $payment->setOrganization($this->getOrganization());
        $payment->setReport($this->report);
        $payment->setManager($this->manager);
        $payment->setKKMBuilder($this->kkmBuilder);
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