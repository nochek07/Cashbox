<?php

namespace Cashbox\BoxBundle\Document;

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
     * @MongoDB\Field(type="hash")
     */
    protected $dataKomtet;

    /**
     * @MongoDB\Field(type="hash")
     */
    protected $dataSberbank;

    /**
     * @MongoDB\Field(type="hash")
     */
    protected $dataYandex;

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
     * Set iNN
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
     * Set dataSberbank
     *
     * @param array $dataSberbank
     * @return self
     */
    public function setDataSberbank($dataSberbank)
    {
        $this->dataSberbank = $dataSberbank;
        return $this;
    }

    /**
     * Get dataSberbank
     *
     * @return array $dataSberbank
     */
    public function getDataSberbank()
    {
        return $this->dataSberbank;
    }

    /**
     * Set dataYandex
     *
     * @param array $dataYandex
     * @return self
     */
    public function setDataYandex($dataYandex)
    {
        $this->dataYandex = $dataYandex;
        return $this;
    }

    /**
     * Get dataYandex
     *
     * @return array $dataYandex
     */
    public function getDataYandex()
    {
        return $this->dataYandex;
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
     * Get $secret
     *
     * @return string $secret
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName() ?? '-';
    }
}