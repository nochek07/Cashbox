<?php

namespace Cashbox\BoxBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;

/**
 * @MongoDB\Document(collection="ReportKomtet")
 */
class ReportKomtet
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
    protected $dataKomtet;

    /**
     * @MongoDB\Field(type="hash")
     */
    protected $dataPost;

    /**
     * @MongoDB\Field(type="int")
     */
    protected $inn;

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
     * @return \DateTime $datetime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return self
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Get state
     *
     * @return string $state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set uuid
     *
     * @param string $uuid
     * @return self
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * Get uuid
     *
     * @return string $uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return self
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Get action
     *
     * @return string $action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set dataKomtet
     *
     * @param array $dataKomtet
     * @return self
     */
    public function setDataKomtet($dataKomtet)
    {
        $this->dataKomtet = $dataKomtet;
        return $this;
    }

    /**
     * Get dataKomtet
     *
     * @return array $dataKomtet
     */
    public function getDataKomtet()
    {
        return $this->dataKomtet;
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

    /**
     * Set inn
     *
     * @param integer $inn
     * @return self
     */
    public function setInn($inn)
    {
        $this->inn = $inn;
        return $this;
    }

    /**
     * Get inn
     *
     * @return integer $inn
     */
    public function getInn()
    {
        return $this->inn;
    }

    /**
     * @return Id|string
     */
    public function __toString()
    {
        return $this->getId() ?? '-';
    }
}
