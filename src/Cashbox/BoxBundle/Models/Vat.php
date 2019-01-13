<?php

namespace Cashbox\BoxBundle\Models;

class Vat extends \Komtet\KassaSdk\Vat
{
    public static $choices = [
        'Без НДС' => self::RATE_NO,
        '0%' => self::RATE_0,
        '10%' => self::RATE_10,
        '18%' => self::RATE_18,
        '20%' => self::RATE_20
    ];
}