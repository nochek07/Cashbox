<?php

namespace Cashbox\BoxBundle\Document;

use Cashbox\BoxBundle\Model\Type;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
* @MongoDB\EmbeddedDocument
*/
class Payment extends PaymentDocumentAbstract
{
    public function getArrayForAdmin(): array
    {
        return Type\PaymentTypes::getArrayForAdmin();
    }
}