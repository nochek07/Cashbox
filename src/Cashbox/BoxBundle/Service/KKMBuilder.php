<?php

namespace Cashbox\BoxBundle\Service;

use Cashbox\BoxBundle\Document\{Organization, KKM};
use Cashbox\BoxBundle\Model\KKM\{KKMInterface, KKMAbstract};

class KKMBuilder
{
    /**
     * @var Report $report
     */
    private $report;

    /**
     * @var Mailer $mailer
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
     * @param string $name
     * @param Organization $Organization
     * @param KKM $kkmDocument
     * @return null
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
     * @param KKMAbstract $kkmManager
     * @return KKMInterface
     */
    public function getKKMWithOptions(KKMAbstract $kkmManager)
    {
        $kkmManager->setReport($this->report);
        $kkmManager->setMailer($this->mailer);
        return $kkmManager;
    }
}