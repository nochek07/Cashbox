<?php

namespace Cashbox\BoxBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="organizations")
 */
class Organization
{
    /**
     * @MongoDB\Id(strategy="AUTO")
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $name;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $tin;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $adminEmail;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $secret;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $patternNomenclature;

    /**
     * @MongoDB\EmbedMany(targetDocument="Cashbox\BoxBundle\Document\Payment")
     */
    protected $payments;

    /**
     * @MongoDB\EmbedMany(targetDocument="Cashbox\BoxBundle\Document\Till")
     */
    protected $tills;

    /**
     * @MongoDB\EmbedMany(targetDocument="Cashbox\BoxBundle\Document\Other")
     */
    protected $others;

    /**
     * Organization constructor.
     */
    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->tills = new ArrayCollection();
        $this->others = new ArrayCollection();
    }

    /**
     * Get ID
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set TIN
     *
     * @param string $tin
     *
     * @return self
     */
    public function setTin(string $tin): self
    {
        $this->tin = $tin;
        return $this;
    }

    /**
     * Get TIN
     */
    public function getTin(): string
    {
        return $this->tin;
    }

    /**
     * Set adminEmail
     *
     * @param string $adminEmail
     *
     * @return self
     */
    public function setAdminEmail(string $adminEmail): self
    {
        $this->adminEmail = $adminEmail;
        return $this;
    }

    /**
     * Get adminEmail
     */
    public function getAdminEmail(): string
    {
        return $this->adminEmail;
    }

    /**
     * Set secret
     *
     * @param string $secret
     *
     * @return self
     */
    public function setSecret(string $secret): self
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * Get secret
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * Set Nomenclature pattern
     *
     * @param string $patternNomenclature
     *
     * @return self
     */
    public function setPatternNomenclature(string $patternNomenclature): self
    {
        $this->patternNomenclature = $patternNomenclature;
        return $this;
    }

    /**
     * Get patternNomenclature
     */
    public function getPatternNomenclature(): string
    {
        return $this->patternNomenclature;
    }

    /**
     * Add payment
     *
     * @param $payment
     */
    public function addPayment($payment): void
    {
        $this->payments[] = $payment;
    }

    /**
     * Remove payment
     *
     * @param $payment
     */
    public function removePayment($payment): void
    {
        $this->payments->removeElement($payment);
    }

    /**
     * Get payments
     *
     * @return ArrayCollection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Add Till
     *
     * @param $till
     */
    public function addTill($till): void
    {
        $this->tills[] = $till;
    }

    /**
     * Remove Till
     *
     * @param $till
     */
    public function removeTill($till): void
    {
        $this->tills->removeElement($till);
    }

    /**
     * Get Tills
     *
     * @return ArrayCollection
     */
    public function getTills()
    {
        return $this->tills;
    }

    /**
     * Add others
     *
     * @param $other
     */
    public function addOther($other): void
    {
        $this->others[] = $other;
    }

    /**
     * Remove others
     *
     * @param $other
     */
    public function removeOther($other): void
    {
        $this->others->removeElement($other);
    }

    /**
     * Get others
     *
     * @return ArrayCollection
     */
    public function getOthers()
    {
        return $this->others;
    }

    public function __toString(): string
    {
        return $this->getName() ?? '-';
    }
}