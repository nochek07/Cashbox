<?php

namespace Cashbox\BoxBundle\Document;

use Cashbox\BoxBundle\Model\Type;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
* @MongoDB\EmbeddedDocument
*/
class Other extends AbstractPaymentDocument
{
    public function getArrayForAdmin(): array
    {
        return Type\OtherTypes::getArrayForAdmin();
    }
}