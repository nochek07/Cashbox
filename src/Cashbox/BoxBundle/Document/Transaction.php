<?php

namespace Cashbox\BoxBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="transactions")
 */
class Transaction
{
    /**
     * @MongoDB\Id(strategy="AUTO")
     */
    protected $id;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $datetime;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $action;

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
     * @MongoDB\Field(type="string")
     */
    protected $tin;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $type;

    /**
     * Get ID
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set datetime
     */
    public function setDatetime(): self
    {
        $this->datetime = new \DateTime();
        return $this;
    }

    /**
     * Get datetime
     */
    public function getDatetime(): \DateTime
    {
        return $this->datetime;
    }

    /**
     * Set action
     *
     * @param string $action
     *
     * @return self
     */
    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Get action
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Set Sum
     *
     * @param float $Sum
     *
     * @return self
     */
    public function setSum(float $Sum): self
    {
        $this->Sum = $Sum;
        return $this;
    }

    /**
     * Get Sum
     */
    public function getSum(): float
    {
        return $this->Sum;
    }

    /**
     * Set customer Number
     *
     * @param string $customerNumber
     *
     * @return self
     */
    public function setCustomerNumber(string $customerNumber): self
    {
        $this->customerNumber = $customerNumber;
        return $this;
    }

    /**
     * Get customer Number
     */
    public function getCustomerNumber(): string
    {
        return $this->customerNumber ?? '-';
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set dataPost
     *
     * @param array $dataPost
     *
     * @return self
     */
    public function setDataPost(array $dataPost): self
    {
        $this->dataPost = $dataPost;
        return $this;
    }

    /**
     * Get dataPost
     */
    public function getDataPost(): array
    {
        return $this->dataPost;
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
     * Set type
     *
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type): string
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function __toString(): string
    {
        return $this->getId() ?? '-';
    }
}