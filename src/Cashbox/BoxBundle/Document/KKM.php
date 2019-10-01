<?php

namespace Cashbox\BoxBundle\Document;

use Cashbox\BoxBundle\Model\Type\KKMTypes;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
* @MongoDB\EmbeddedDocument
*/
class KKM extends AbstractObjectDocument
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
     * @return string
     */
    public function __toString()
    {
        return ($this->getName() ?? '-') . ' (' . ($this->getType() ?? '-') . ')';
    }

    public function getArrayForAdmin(): array
    {
        return KKMTypes::getArrayForAdmin();
    }
}