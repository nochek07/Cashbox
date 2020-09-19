<?php

namespace Cashbox\BoxBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *     collection="tillReports",
 *     repositoryClass="Cashbox\BoxBundle\Repository\TillReportRepository"
 * )
 */
class TillReport
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
    protected $type;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $typePayment;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $state;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $uuid;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $action;

    /**
     * @MongoDB\Field(type="hash")
     */
    protected $dataTill = [];

    /**
     * @MongoDB\Field(type="hash")
     */
    protected $dataPost = [];

    /**
     * @MongoDB\Field(type="string")
     */
    protected $tin;

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
     * Get Sum
     */
    public function getDatetime(): \DateTime
    {
        return $this->datetime;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type): self
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

    /**
     * Set type payment
     *
     * @param string $typePayment
     *
     * @return self
     */
    public function setTypePayment(string $typePayment): self
    {
        $this->typePayment = $typePayment;
        return $this;
    }

    /**
     * Get type payment
     */
    public function getTypePayment(): string
    {
        return $this->typePayment;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return self
     */
    public function setState(string $state): self
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Get state
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * Set UUID
     *
     * @param string $uuid
     *
     * @return self
     */
    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * Get UUID
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
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
     * Set dataTill
     *
     * @param array $dataTill
     *
     * @return self
     */
    public function setDataTill(array $dataTill): self
    {
        $this->dataTill = $dataTill;
        return $this;
    }

    /**
     * Get dataTill
     */
    public function getDataTill(): array
    {
        return $this->dataTill;
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

    public function __toString(): string
    {
        return $this->getId() ?? '-';
    }
}