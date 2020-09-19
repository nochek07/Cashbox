<?php

namespace Cashbox\BoxBundle\Model\Type;

class OtherTypes extends AbstractTypes
{
    const PAYMENT_TYPE_1C = "1C";

    /**
     * {@inheritDoc}
     */
    public static function getArrayForAdmin(): array
    {
        return [
            self::PAYMENT_TYPE_1C => [],
        ];
    }
}