<?php

namespace Cashbox\BoxBundle\Model\KKM;

use Cashbox\BoxBundle\DependencyInjection\Mailer;
use Cashbox\BoxBundle\Document\Organization;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

abstract class KKMAbstract implements KKMInterface
{
    /**
     * @var array $data
     */
    protected $data;

    /**
     * @var ManagerRegistry $manager
     */
    private $manager;

    /**
     * @var Organization $Organization
     */
    private $Organization;

    /**
     * @var Mailer|null $mailer
     */
    private $mailer = null;

    /**
     * KKMAbstract constructor.
     *
     * @param Organization $Organization
     * @param ManagerRegistry $manager
     */
    public function __construct(Organization $Organization, ManagerRegistry $manager)
    {
        $this->Organization = $Organization;
        $this->manager = $manager;
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
     * Get Organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->Organization;
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
    abstract function connect();

    /**
     * {@inheritDoc}
     */
    abstract function isQueueActive($id);
}