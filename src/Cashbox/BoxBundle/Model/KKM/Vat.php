<?php

namespace Cashbox\BoxBundle\Model\KKM;

class Vat extends \Komtet\KassaSdk\Vat
{
    public static $choices = [
        'rate.no' => self::RATE_NO,
        'rate.0' => self::RATE_0,
        'rate.10' => self::RATE_10,
        'rate.18' => self::RATE_18,
        'rate.20' => self::RATE_20
    ];
}