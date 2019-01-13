<?php

namespace Cashbox\BoxBundle\Models;

class TaxSystem extends \Komtet\KassaSdk\TaxSystem
{
    public static $choices = [
        'ОСНО' => self::COMMON,
        'УСН (Доходы)' => self::SIMPLIFIED_IN,
        'УСН (Доходы-Расходы)' => self::SIMPLIFIED_IN_OUT,
        'ЕНВД' => self::UTOII,
        'ЕСН' => self::UST,
        'Патент' => self::PATENT
    ];
}