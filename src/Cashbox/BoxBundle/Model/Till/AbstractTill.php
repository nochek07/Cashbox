<?php

namespace Cashbox\BoxBundle\Model\Till;

use Cashbox\BoxBundle\Document\{Organization, Till};
use Cashbox\BoxBundle\Service\{Mailer, Report};

abstract class AbstractTill implements TillInterface
{
    protected $name = '';

    /**
     * @var Organization
     */
    protected $organizationDocument;

    /**
     * @var Till
     */
    protected $tillDocument;

    /**
     * @var Report
     */
    private $report;

    /**
     * @var Mailer|null
     */
    private $mailer = null;

    /**
     * AbstractTill constructor.
     *
     * @param Organization $organizationDocument
     * @param Till $tillDocument
     */
    public function __construct(Organization $organizationDocument, Till $tillDocument)
    {
        $this->organizationDocument = $organizationDocument;
        $this->tillDocument = $tillDocument;
    }

    /**
     * Set Report
     *
     * @param Report $report
     * @return self
     */
    public function setReport(Report $report): self
    {
        $this->report = $report;
        return $this;
    }

    /**
     * Get Report
     */
    public function getReport(): Report
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
    public function setMailer(Mailer $mailer): self
    {
        $this->mailer = $mailer;
        return $this;
    }

    /**
     * Get Mailer
     */
    public function getMailer(): ?Mailer
    {
        return $this->mailer;
    }

    /**
     * {@inheritDoc}
     */
    abstract function connect(): bool;

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
    abstract function checkTill(): bool;
}