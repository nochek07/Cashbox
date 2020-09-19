<?php

namespace Cashbox\BoxBundle\Document;

use Cashbox\BoxBundle\Model\Type\TillTypes;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
* @MongoDB\EmbeddedDocument
*/
class Till extends AbstractObjectDocument
{
    /**
     * @MongoDB\Field(type="string")
     */
    protected $name;

    /**
     * Set name
     *
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return ($this->getName() ?? '-') . ' (' . ($this->getType() ?? '-') . ')';
    }

    public function getArrayForAdmin(): array
    {
        return TillTypes::getArrayForAdmin();
    }
}