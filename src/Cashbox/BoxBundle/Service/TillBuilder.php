<?php

namespace Cashbox\BoxBundle\Service;

use Cashbox\BoxBundle\Document\{Organization, Till};
use Cashbox\BoxBundle\Model\Till\{AbstractTill, TillInterface};

class TillBuilder
{
    /**
     * @var Report
     */
    private $report;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * TillBuilder constructor.
     *
     * @param Report $report
     * @param Mailer $mailer
     */
    public function __construct(Report $report, Mailer $mailer)
    {
        $this->report = $report;
        $this->mailer = $mailer;
    }

    /**
     * Factory
     *
     * @param string $name
     * @param Organization $Organization
     * @param Till $tillDocument
     *
     * @return AbstractTill|null
     */
    public function create(string $name, Organization $Organization, Till $tillDocument)
    {
        try {
            return $name($Organization, $tillDocument);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get TillInterface with Options
     *
     * @param AbstractTill $tillManager
     *
     * @return TillInterface
     */
    public function getTillWithOptions(AbstractTill $tillManager)
    {
        $tillManager->setReport($this->report);
        $tillManager->setMailer($this->mailer);
        return $tillManager;
    }
}