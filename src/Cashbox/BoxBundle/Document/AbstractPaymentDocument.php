<?php

namespace Cashbox\BoxBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

abstract class AbstractPaymentDocument extends AbstractObjectDocument
{
    /**
     * @MongoDB\ReferenceOne(targetDocument="Cashbox\BoxBundle\Document\KKM", nullable=true)
     */
    protected $kkm = null;

    /**
     * Set KKM
     *
     * @param KKM|null $kkm
     *
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
}