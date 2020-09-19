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
     * Get ID
     */
    public function getId(): string 
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
     * Set data
     *
     * @param array $data
     *
     * @return self
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param $name
     *
     * @return array
     */
    public function __get($name): array
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
    public function __set($name, array $data): self
    {
        if (array_key_exists($name, $this->getArrayForAdmin())) {
            $this->additional[$name] = $data;
            return $this;
        }
        throw new BadMethodCallException;
    }

    /**
     * Get Additional
     */
    public function getAdditional(): array
    {
        return $this->additional;
    }

    abstract function getArrayForAdmin(): array;
}