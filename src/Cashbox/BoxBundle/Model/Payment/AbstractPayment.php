<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Service\{Report, TillBuilder};
use Cashbox\BoxBundle\Document\{AbstractPaymentDocument, Organization, Till};
use Cashbox\BoxBundle\Model\{Till\AbstractTill, Till\TillInterface, Type\TillTypes};
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractPayment implements PaymentInterface
{
    protected $name = '';

    /**
     * @var ManagerRegistry
     */
    private $manager;

    /**
     * @var Organization
     */
    protected $organization;

    /**
     * @var Report
     */
    private $report;

    /**
     * @var TillBuilder
     */
    private $tillBuilder;

    /**
     * {@inheritDoc}
     */
    abstract public function send(Request $request);

    /**
     * {@inheritDoc}
     */
    abstract public function check(Request $request);

    /**
     * Set Organization
     *
     * @param Organization $organization
     */
    public function setOrganization(Organization $organization)
    {
        $this->organization = $organization;
    }

    /**
     * Set Manager
     *
     * @param ManagerRegistry $manager
     */
    public function setManager(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get Manager
     *
     * @return ManagerRegistry
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Set Report
     *
     * @param Report $report
     */
    public function setReport(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Get Report
     *
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Set Till Builder
     *
     * @param TillBuilder $tillBuilder
     */
    public function setTillBuilder(TillBuilder $tillBuilder)
    {
        $this->tillBuilder = $tillBuilder;
    }

    /**
     * Get Till Builder
     *
     * @return TillBuilder
     */
    public function getTillBuilder()
    {
        return $this->tillBuilder;
    }

    /**
     * Additional check
     *
     * @param Request $request
     * @param string $handlingSecret secret word
     *
     * @return bool
     */
    public function otherCheckMD5(Request $request, string $handlingSecret): bool
    {
        return ($request->get('h') != md5($request->get('customerNumber') . "_"
                . $request->get('orderSumAmount') . "_" . $handlingSecret));
    }

    /**
     * Get desired payment
     *
     * @param Collection $mongoPersistCollection
     *
     * @return AbstractPaymentDocument|null
     */
    public function getDesiredPayment(Collection $mongoPersistCollection): ?AbstractPaymentDocument
    {
        $payments = $mongoPersistCollection->filter(
            function (AbstractPaymentDocument $entry) {
                return $entry->getType() === $this->name;
            }    
        );

        if ($payments->count() > 0) {
            return $payments->first();
        }
        return null;
    }

    /**
     * Get Till by Payment
     *
     * @param AbstractPaymentDocument $payment
     *
     * @return TillInterface|null
     */
    public function getTillByPayment(AbstractPaymentDocument $payment): ?TillInterface
    {
        $tillDocument = $payment->getTill();
        if ($tillDocument instanceof Till) {
            /**
             * @var ArrayCollection $mongoPersistCollection
             */
            $mongoPersistCollection = $this->organization->getTills();
            $tills = $mongoPersistCollection->filter(
                function (Till $entry) use ($tillDocument) {
                    return $entry->getId() === $tillDocument->getId();
                }
            );

            if ($tills->count() > 0) {
                $tillDocument = $tills->first();
                $tillClass = TillTypes::$arrayTillModelClass[$tillDocument->getType()];

                $tillManager = $this->tillBuilder
                    ->create($tillClass, $this->organization, $tillDocument);
                if ($tillManager instanceof AbstractTill) {
                    return $this->tillBuilder->getTillWithOptions($tillManager);
                }
            }
        }
        return null;
    }
}