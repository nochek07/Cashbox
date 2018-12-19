<?php

namespace Cashbox\BoxBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;
use Doctrine\ODM\MongoDB\Mapping\Annotations\date;

/**
 * @Annotation
 * @MongoDB/Document
 *
 */
class SberbankTransaction
{
    /**
     * @MongoDB\Id(strategy="INCREMENT")
     */
    protected $id;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $datetime;

    /**
     * @MongoDB\Field(type="float")
     */
    protected $Sum;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $customerNumber;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $email;

    /**
     * @MongoDB\Field(type="hash")
     */
    protected $dataPost;

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
     * Set datetime
     *
     * @return self
     */
    public function setDatetime()
    {
        $this->datetime = new \DateTime();
        return $this;
    }

    /**
     * Get Sum
     *
     * @return date $datetime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set Sum
     *
     * @param float $Sum
     * @return self
     */
    public function setSum($Sum)
    {
        $this->Sum = $Sum;
        return $this;
    }

    /**
     * Get Sum
     *
     * @return float $Sum
     */
    public function getSum()
    {
        return $this->Sum;
    }

    /**
     * Set customerNumber
     *
     * @param string $customerNumber
     * @return self
     */
    public function setCustomerNumber($customerNumber)
    {
        $this->customerNumber = $customerNumber;
        return $this;
    }

    /**
     * Get customerNumber
     *
     * @return string $customerNumber
     */
    public function getCustomerNumber()
    {
        return $this->customerNumber;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set dataPost
     *
     * @param array $dataPost
     * @return self
     */
    public function setDataPost($dataPost)
    {
        $this->dataPost = $dataPost;
        return $this;
    }

    /**
     * Get dataPost
     *
     * @return array $dataPost
     */
    public function getDataPost()
    {
        return $this->dataPost;
    }
}
