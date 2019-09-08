<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Model\BoxTypes;

class OtherTypes extends BoxTypes
{
    private static $translationDomain = 'BoxBundle';

    /**
     * Get ArrayForAdmin
     *
     * @return array
     */
    public static function getArrayForAdmin()
    {
        return [
            '1C' => [
            ],
        ];
    }
}