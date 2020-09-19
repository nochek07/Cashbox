<?php

namespace Cashbox\BoxBundle\Service;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Model\OrganizationModel;
use Cashbox\BoxBundle\Model\Payment\AbstractPayment;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class Box
{
    /**
     * @var Organization|null
     */
    private $Organization;

    /**
     * @var string
     */
    private $OrganizationTextError = '';

    /**
     * @var ManagerRegistry
     */
    private $manager;

    /**
     * @var Report
     */
    private $report;

    /**
     * @var TillBuilder
     */
    private $tillBuilder;

    /**
     * Box constructor.
     * 
     * @param ManagerRegistry $manager
     * @param Report $report
     * @param TillBuilder $tillBuilder
     */
    public function __construct(ManagerRegistry $manager, Report $report, TillBuilder $tillBuilder)
    {
        $this->manager = $manager;
        $this->report = $report;
        $this->tillBuilder = $tillBuilder;
    }

    /**
     * Checking
     *
     * @param Request $request
     * @param AbstractPayment $payment
     *
     * @return string
     */
    public function check(Request $request, AbstractPayment $payment)
    {
        $this->defineOrganization($request);
        return $this->getResponseTextCheck($request, $payment);
    }

    /**
     * Sending
     *
     * @param Request $request
     * @param AbstractPayment $payment
     *
     * @return string
     */
    public function send(Request $request, AbstractPayment $payment)
    {
        $this->defineOrganization($request);
        return $this->getResponseTextSend($request, $payment);
    }

    /**
     * Get Response Text for Check
     *
     * @param Request $request
     * @param AbstractPayment $payment
     *
     * @return string
     */
    public function getResponseTextCheck(Request $request, AbstractPayment $payment)
    {
        if ($this->getOrganization() instanceof Organization) {
            $this->setOptionsPayment($payment);
            return $payment->check($request);
        } else {
            return $this->OrganizationTextError;
        }
    }

    /**
     * Get Response Text for Send
     *
     * @param Request $request
     * @param AbstractPayment $payment
     * 
     * @return string
     */
    public function getResponseTextSend(Request $request, AbstractPayment $payment)
    {
        if ($this->getOrganization() instanceof Organization) {
            $this->setOptionsPayment($payment);
            return $payment->send($request);
        } else {
            return $this->OrganizationTextError;
        }
    }

    /**
     * Set payment options
     *
     * @param AbstractPayment $payment
     */
    public function setOptionsPayment(AbstractPayment &$payment)
    {
        $payment->setOrganization($this->getOrganization());
        $payment->setReport($this->report);
        $payment->setManager($this->manager);
        $payment->setTillBuilder($this->tillBuilder);
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
     * Get Organization
     * 
     * @return Organization|null
     */
    public function getOrganization()
    {
        return $this->Organization;
    }

    /**
     * Set text error of Organization
     *
     * @param $text
     */
    public function setOrganizationTextError($text)
    {
        $this->OrganizationTextError = $text;
    }
}