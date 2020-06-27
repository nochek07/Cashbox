<?php

namespace Cashbox\BoxBundle\Document;

use BadMethodCallException;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

abstract class AbstractObjectDocument
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
     * @var array
     */
    protected $additional = [];

    /**
     * Get id
     *
     * @return MongoDB\id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     *
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
     * Set data
     *
     * @param array $data
     *
     * @return self
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
     *
     * @return array
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->getArrayForAdmin())) {
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
     *
     * @return self
     */
    public function __set($name, $data)
    {
        if (array_key_exists($name, $this->getArrayForAdmin())) {
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

    abstract function getArrayForAdmin(): array;
}