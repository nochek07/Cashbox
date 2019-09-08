<?php

namespace Cashbox\BoxBundle\Document;

use BadMethodCallException;
use Cashbox\BoxBundle\Model\Payment\OtherTypes;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;

/**
* @MongoDB\EmbeddedDocument
*/
class Other
{
    /**
     * @MongoDB\Id(strategy="AUTO")
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $type;

    /**
     * @MongoDB\Field(type="hash")
     */
    protected $data = [];

    /**
     * @MongoDB\ReferenceOne(targetDocument="KKM", nullable=true)
     */
    protected $kkm = null;

    /**
     * @var array $additional
     */
    protected $additional = [];

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
     * Set type
     *
     * @param string $type
     * @return $this
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
     * Set data
     *
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get data
     *
     * @return array $data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set KKM
     *
     * @param KKM|null $kkm
     * @return $this
     */
    public function setKkm($kkm)
    {
        $this->kkm = $kkm;
        return $this;
    }

    /**
     * Get KKM
     *
     * @return KKM|null $kkm
     */
    public function getKkm()
    {
        return $this->kkm;
    }

    /**
     * @param $name
     * @return array
     */
    public function __get($name)
    {
        if (array_key_exists($name, OtherTypes::getArrayForAdmin())) {
            if ($this->getType() === $name) {
                return $this->getData();

            } else {
                return [];
            }
        }
        throw new BadMethodCallException;
    }

    /**
     * @param $name
     * @param array $data
     * @return self
     */
    public function __set($name, $data)
    {
        if (array_key_exists($name, OtherTypes::getArrayForAdmin())) {
            $this->additional[$name] = $data;
            return $this;
        }
        throw new BadMethodCallException;
    }

    /**
     * Get Additional
     *
     * @return array
     */
    public function getAdditional()
    {
        return $this->additional;
    }
}
