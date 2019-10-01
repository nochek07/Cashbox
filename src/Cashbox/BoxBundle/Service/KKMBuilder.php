<?php

namespace Cashbox\BoxBundle\Service;

use Cashbox\BoxBundle\Document\{Organization, KKM};
use Cashbox\BoxBundle\Model\KKM\{KKMInterface, AbstractKKM};

class KKMBuilder
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
     * KKMBuilder constructor.
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
     * @param KKM $kkmDocument
     *
     * @return AbstractKKM|null
     */
    public function create(string $name, Organization $Organization, KKM $kkmDocument)
    {
        try {
            return $name($Organization, $kkmDocument);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get KKMInterface with Options
     *
     * @param AbstractKKM $kkmManager
     *
     * @return KKMInterface
     */
    public function getKKMWithOptions(AbstractKKM $kkmManager)
    {
        $kkmManager->setReport($this->report);
        $kkmManager->setMailer($this->mailer);
        return $kkmManager;
    }
}