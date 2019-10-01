<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Service\{KKMBuilder, Report};
use Cashbox\BoxBundle\Document\{KKM, Organization, AbstractPaymentDocument};
use Cashbox\BoxBundle\Model\KKM\{AbstractKKM, KKMInterface};
use Cashbox\BoxBundle\Model\Type\KKMTypes;
use Doctrine\Common\Collections\{Collection, ArrayCollection};
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
    protected $Organization;

    /**
     * @var Report
     */
    private $report;

    /**
     * @var KKMBuilder
     */
    private $kkmBuilder;

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
     * @param Organization $Organization
     */
    public function setOrganization(Organization $Organization)
    {
        $this->Organization = $Organization;
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
     * Set KKMBuilder
     *
     * @param KKMBuilder $kkmBuilder
     */
    public function setKKMBuilder(KKMBuilder $kkmBuilder)
    {
        $this->kkmBuilder = $kkmBuilder;
    }

    /**
     * Get KKMBuilder
     *
     * @return KKMBuilder
     */
    public function getKKMBuilder()
    {
        return $this->kkmBuilder;
    }

    /**
     * Additional check
     *
     * @param Request $request
     * @param String $handling_secret - secret word
     *
     * @return bool
     */
    public function otherCheckMD5(Request $request, $handling_secret)
    {
        return ($request->get('h') != md5($request->get('customerNumber') . "_"
                . $request->get('orderSumAmount') . "_" . $handling_secret));
    }

    /**
     * Get desired payment
     *
     * @param Collection $mongoPersistCollection
     *
     * @return AbstractPaymentDocument|null
     */
    public function getDesiredPayment(Collection $mongoPersistCollection)
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
     * Get KKM By Payment
     *
     * @param AbstractPaymentDocument $payments
     *
     * @return KKMInterface|null
     */
    public function getKkmByPayment(AbstractPaymentDocument $payments)
    {
        $kkmDocument = $payments->getKkm();
        if ($kkmDocument instanceof KKM) {
            /**
             * @var ArrayCollection $mongoPersistCollection
             */
            $mongoPersistCollection = $this->Organization->getKKMs();
            $kkms = $mongoPersistCollection->filter(
                function (KKM $entry) use ($kkmDocument) {
                    return $entry->getId() === $kkmDocument->getId();
                }
            );

            if ($kkms->count() > 0) {
                $kkmDocument = $kkms->first();
                $classKKM = KKMTypes::$arrayKkmModelClass[$kkmDocument->getType()];

                $kkmManager = $this->kkmBuilder
                    ->create($classKKM, $this->Organization, $kkmDocument);
                if ($kkmManager instanceof AbstractKKM) {
                    return $this->kkmBuilder->getKKMWithOptions($kkmManager);
                }
            }
        }
        return null;
    }
}