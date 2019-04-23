<?php

namespace Cashbox\BoxBundle\Model;

class TaxSystem extends \Komtet\KassaSdk\TaxSystem
{
    public static $choices = [
        'tax.commom' => self::COMMON,
        'tax.simplified.in' => self::SIMPLIFIED_IN,
        'tax.simplified.in.out' => self::SIMPLIFIED_IN_OUT,
        'tax.utoii' => self::UTOII,
        'tax.ust' => self::UST,
        'tax.patent' => self::PATENT
    ];
}