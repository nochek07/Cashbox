<?php

namespace Cashbox\BoxBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

abstract class AbstractPaymentDocument extends AbstractObjectDocument
{
    /**
     * @MongoDB\ReferenceOne(targetDocument="Cashbox\BoxBundle\Document\Till", nullable=true)
     */
    protected $till = null;

    /**
     * Set Till
     *
     * @param Till|null $till
     *
     * @return self
     */
    public function setTill(?Till $till): self
    {
        $this->till = $till;
        return $this;
    }

    /**
     * Get Till
     */
    public function getTill(): ?Till
    {
        return $this->till;
    }
}