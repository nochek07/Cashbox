<?php

namespace Cashbox\BoxBundle\Document;

use BadMethodCallException;
use Cashbox\BoxBundle\Model\KKM\KKMTypes;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;

/**
* @MongoDB\EmbeddedDocument
*/
class KKM
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
    protected $type;

    /**
     * @MongoDB\Field(type="hash")
     */
    protected $data = [];

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
     * Set name
     *
     * @param string $name
     * @return $this
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
     * @param $name
     * @return array
     */
    public function __get($name)
    {
        if (array_key_exists($name, KKMTypes::getArrayForAdmin())) {
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
        if (array_key_exists($name, KKMTypes::getArrayForAdmin())) {
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

    /**
     * @return string
     */
    public function __toString()
    {
        return ($this->getName() ?? '-') . ' (' . ($this->getType() ?? '-') . ')';
    }
}
