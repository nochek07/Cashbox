<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Service\{KKMBuilder, Report};
use Cashbox\BoxBundle\Document\{KKM, Organization, PaymentDocumentAbstract};
use Cashbox\BoxBundle\Model\KKM\{KKMAbstract, KKMInterface};
use Cashbox\BoxBundle\Model\Type\KKMTypes;
use Doctrine\Common\Collections\{Collection, ArrayCollection};
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

abstract class PaymentAbstract implements PaymentInterface
{
    protected $name = '';

    /**
     * @var ManagerRegistry $manager
     */
    private $manager;

    /**
     * @var Organization $Organization
     */
    protected $Organization;

    /**
     * @var Report $report
     */
    private $report;

    /**
     * @var KKMBuilder $kkmBuilder
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
     * @param Organization $Organization
     */
    public function setOrganization(Organization $Organization)
    {
        $this->Organization = $Organization;
    }

    /**
     * @param ManagerRegistry $manager
     */
    public function setManager(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return ManagerRegistry
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param Report $report
     */
    public function setReport(Report $report)
    {
        $this->report = $report;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param KKMBuilder $kkmBuilder
     */
    public function setKKMBuilder(KKMBuilder $kkmBuilder)
    {
        $this->kkmBuilder = $kkmBuilder;
    }

    /**
     * @return KKMBuilder
     */
    public function getKKMBuilder()
    {
        return $this->kkmBuilder;
    }

    /**
     * Дополнительная проверка
     *
     * @param Request $request
     * @param String $handling_secret - секретное слово
     * @return bool
     */
    public function otherCheckMD5(Request $request, $handling_secret)
    {
        return ($request->get('h') != md5($request->get('customerNumber') . "_"
                . $request->get('orderSumAmount') . "_" . $handling_secret));
    }

    /**
     * @param Collection $mongoPersistCollection
     * @return PaymentDocumentAbstract|null
     */
    public function getDesiredPayment(Collection $mongoPersistCollection)
    {
        $payments = $mongoPersistCollection->filter(
            function (PaymentDocumentAbstract $entry) {
                return $entry->getType() === $this->name;
            }    
        );

        if ($payments->count() > 0) {
            return $payments->first();
        }
        return null;
    }

    /**
     * @param PaymentDocumentAbstract $payments
     * @return KKMInterface|null
     */
    public function getKkmByPayment(PaymentDocumentAbstract $payments)
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
                if ($kkmManager instanceof KKMAbstract) {
                    return $this->kkmBuilder->getKKMWithOptions($kkmManager);
                }
            }
        }
        return null;
    }
}