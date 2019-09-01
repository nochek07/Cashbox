<?php

namespace Cashbox\BoxBundle\Model\KKM;

use Cashbox\BoxBundle\DependencyInjection\Mailer;
use Cashbox\BoxBundle\DependencyInjection\Report;
use Cashbox\BoxBundle\Document\{Organization, KKM};

abstract class KKMAbstract implements KKMInterface
{
    /**
     * @var Organization $organizationDocument
     */
    protected $organizationDocument;

    /**
     * @var KKM $kkmDocument
     */
    protected $kkmDocument;

    /**
     * @var Report $report
     */
    private $report;

    /**
     * @var Mailer|null $mailer
     */
    private $mailer = null;

    /**
     * KKMAbstract constructor.
     *
     * @param Organization $organizationDocument
     * @param KKM $kkmDocument
     */
    public function __construct(Organization $organizationDocument, KKM $kkmDocument)
    {
        $this->organizationDocument = $organizationDocument;
        $this->kkmDocument = $kkmDocument;
    }

    /**
     * Set Report
     *
     * @param Report $report
     * @return self
     */
    public function setReport(Report $report)
    {
        $this->report = $report;
        return $this;
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
     * Set Mailer
     *
     * @param Mailer $mailer
     * @return self
     */
    public function setMailer(Mailer $mailer)
    {
        $this->mailer = $mailer;
        return $this;
    }

    /**
     * Get Mailer
     *
     * @return Mailer|null
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * {@inheritDoc}
     */
    abstract function connect();

    /**
     * {@inheritDoc}
     */
    abstract function buildData(array $param);

    /**
     * {@inheritDoc}
     */
    abstract function send(array $data, string $from);

    /**
     * {@inheritDoc}
     */
    abstract function sendMail(array $data, string $from);

    /**
     * {@inheritDoc}
     */
    abstract function isQueueActive($id);

    /**
     * {@inheritDoc}
     */
    abstract function checkKKM();
}