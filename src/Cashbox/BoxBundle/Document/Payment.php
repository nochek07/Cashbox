<?php

namespace Cashbox\BoxBundle\Document;

use Cashbox\BoxBundle\Model\Payment\PaymentTypes;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
* @MongoDB\EmbeddedDocument
*/
class Payment extends PaymentDocumentAbstract
{
    /**
     * Payment constructor.
     */
    public function __construct()
    {
        $this->arrayForAdmin = PaymentTypes::getArrayForAdmin();
    }
}