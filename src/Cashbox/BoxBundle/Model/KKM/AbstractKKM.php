<?php

namespace Cashbox\BoxBundle\Model\KKM;

use Cashbox\BoxBundle\Service\{Mailer, Report};
use Cashbox\BoxBundle\Document\{Organization, KKM};

abstract class AbstractKKM implements KKMInterface
{
    protected $name = '';

    /**
     * @var Organization
     */
    protected $organizationDocument;

    /**
     * @var KKM
     */
    protected $kkmDocument;

    /**
     * @var Report
     */
    private $report;

    /**
     * @var Mailer|null
     */
    private $mailer = null;

    /**
     * AbstractKKM constructor.
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
     *
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
    abstract function buildData(array $param): array;

    /**
     * {@inheritDoc}
     */
    abstract function send(array $data, string $type);

    /**
     * {@inheritDoc}
     */
    abstract function sendMail(array $data, string $type): bool;

    /**
     * {@inheritDoc}
     */
    abstract function isQueueActive($id): bool;

    /**
     * {@inheritDoc}
     */
    abstract function checkKKM(): bool;
}