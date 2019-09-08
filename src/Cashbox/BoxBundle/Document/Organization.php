<?php

namespace Cashbox\BoxBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;

/**
 * @MongoDB\Document(collection="Organization")
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
     * @MongoDB\Field(type="int")
     */
    protected $INN;

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
     * @MongoDB\EmbedMany(targetDocument="Payment")
     */
    protected $payments;

    /**
     * @MongoDB\EmbedMany(targetDocument="KKM")
     */
    protected $KKMs;

    /**
     * @MongoDB\EmbedMany(targetDocument="Other")
     */
    protected $others;

    /**
     * Organization constructor.
     */
    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->KKMs = new ArrayCollection();
        $this->others = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set INN
     *
     * @param integer $iNN
     * @return self
     */
    public function setINN($iNN)
    {
        $this->INN = $iNN;
        return $this;
    }

    /**
     * Get INN
     *
     * @return integer $iNN
     */
    public function getINN()
    {
        return $this->INN;
    }

    /**
     * Set adminEmail
     *
     * @param string $adminEmail
     * @return self
     */
    public function setAdminEmail($adminEmail)
    {
        $this->adminEmail = $adminEmail;
        return $this;
    }

    /**
     * Get adminEmail
     *
     * @return string $adminEmail
     */
    public function getAdminEmail()
    {
        return $this->adminEmail;
    }

    /**
     * Set secret
     *
     * @param string $secret
     * @return self
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * Get secret
     *
     * @return string $secret
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Set patternNomenclature
     *
     * @param string $patternNomenclature
     * @return self
     */
    public function setPatternNomenclature($patternNomenclature)
    {
        $this->patternNomenclature = $patternNomenclature;
        return $this;
    }

    /**
     * Get patternNomenclature
     *
     * @return string $patternNomenclature
     */
    public function getPatternNomenclature()
    {
        return $this->patternNomenclature;
    }

    /**
     * Add payment
     *
     * @param $payment
     */
    public function addPayment($payment)
    {
        $this->payments[] = $payment;
    }

    /**
     * Remove payment
     *
     * @param $payment
     */
    public function removePayment($payment)
    {
        $this->payments->removeElement($payment);
    }

    /**
     * Get payments
     *
     * @return ArrayCollection $payments
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Add KKM
     *
     * @param $KKM
     */
    public function addKKM($KKM)
    {
        $this->KKMs[] = $KKM;
    }

    /**
     * Remove $KKM
     *
     * @param $KKM
     */
    public function removeKKM($KKM)
    {
        $this->KKMs->removeElement($KKM);
    }

    /**
     * Get KKMs
     *
     * @return ArrayCollection $KKMs
     */
    public function getKKMs()
    {
        return $this->KKMs;
    }

    /**
     * Add others
     *
     * @param $other
     */
    public function addOther($other)
    {
        $this->others[] = $other;
    }

    /**
     * Remove $others
     *
     * @param $other
     */
    public function removeOther($other)
    {
        $this->others->removeElement($other);
    }

    /**
     * Get others
     *
     * @return ArrayCollection $others
     */
    public function getOthers()
    {
        return $this->others;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName() ?? '-';
    }
}
