<?php

namespace Cashbox\BoxBundle\Document;

use Cashbox\BoxBundle\Model\Payment\OtherTypes;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
* @MongoDB\EmbeddedDocument
*/
class Other extends PaymentDocumentAbstract
{
    /**
     * Other payment constructor.
     */
    public function __construct()
    {
        $this->arrayForAdmin = OtherTypes::getArrayForAdmin();
    }
}