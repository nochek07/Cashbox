<?php

namespace Cashbox\BoxBundle\Document;

use Cashbox\BoxBundle\Model\KKM\KKMTypes;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
* @MongoDB\EmbeddedDocument
*/
class KKM extends ObjectDocumentAbstract
{
    /**
     * @MongoDB\Field(type="string")
     */
    protected $name;

    /**
     * KKM constructor.
     */
    public function __construct()
    {
        $this->arrayForAdmin = KKMTypes::getArrayForAdmin();
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
     * @return string
     */
    public function __toString()
    {
        return ($this->getName() ?? '-') . ' (' . ($this->getType() ?? '-') . ')';
    }
}