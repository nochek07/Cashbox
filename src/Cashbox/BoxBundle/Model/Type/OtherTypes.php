<?php

namespace Cashbox\BoxBundle\Model\Type;

class OtherTypes extends AbstractTypes
{
    /**
     * {@inheritDoc}
     */
    public static function getArrayForAdmin()
    {
        return [
            '1C' => [],
        ];
    }
}