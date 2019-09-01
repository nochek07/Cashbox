<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\DependencyInjection\Mailer;
use Cashbox\BoxBundle\DependencyInjection\Report;
use Cashbox\BoxBundle\Document\{KKM, Organization, Payment};
use Cashbox\BoxBundle\Model\KKM\{KKMAbstract, KKMInterface, KKMTypes};
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\MongoDB\PersistentCollection;
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
     * @var Mailer $mailer
     */
    private $mailer;

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
     * @param Mailer $mailer
     */
    public function setMailer(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @return Mailer
     */
    public function getMailer()
    {
        return $this->mailer;
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
     * @return Payment|null
     */
    public function getDataPayment()
    {
        /**
         * @var PersistentCollection $mongoPersistCollection
         */
        $mongoPersistCollection = $this->Organization->getPayments();
        $payments = $mongoPersistCollection->filter(
            function (Payment $entry) {
                return $entry->getType() === $this->name;
            }    
        );

        if ($payments->count() > 0) {
            return $payments->first();
        }
        return null;
    }

    /**
     * @param Payment $payments
     * @return KKMInterface|null
     */
    public function getKKM(Payment $payments)
    {
        $kkmDocument = $payments->getKkm();
        if ($kkmDocument instanceof KKM) {
            /**
             * @var PersistentCollection $mongoPersistCollection
             */
            $mongoPersistCollection = $this->Organization->getKKMs();

            $kkms = $mongoPersistCollection->filter(
                function (KKM $entry) use ($kkmDocument) {
                    return $entry->getId() === $kkmDocument->getId();
                }
            );
            if ($kkms->count() > 0) {
                $kkmDocument = $kkms->first();
                print_r($kkmDocument->getType());
                $classKKM = KKMTypes::$arrayKkmModelClass[$kkmDocument->getType()];
                /**
                 * @var KKMAbstract $kkmManager
                 */
                $kkmManager = new $classKKM($this->Organization, $kkmDocument);
                $kkmManager->setReport($this->getReport());
                $kkmManager->setMailer($this->getMailer());
                return $kkmManager;
            }
        }
        return null;
    }
}