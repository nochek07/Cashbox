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
     * @param Mailer $mailer
     * @return self
     */
    public function setMailer(Mailer $mailer)
    {
        $this->mailer = $mailer;
        return $this;
    }

    /**
     * @return Mailer|null
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->Organization;
    }

    /**
     * @return ManagerRegistry
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param array $param
     * @return mixed
     */
    abstract function buildData(array $param);

    /**
     * @param array $data
     * @param string $from
     * @return mixed
     */
    abstract function send(array $data, string $from);

    /**
     * @param array $data
     * @param string $from
     * @return bool
     */
    abstract function sendMail(array $data, string $from);

    /**
     * @return bool
     */
    abstract function connect();

    /**
     * @param mixed $id
     * @return mixed
     */
    abstract function isQueueActive($id);
}