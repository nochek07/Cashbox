<?php

namespace Cashbox\BoxBundle\Model\Payment;

use Cashbox\BoxBundle\Model\BoxTypes;
use Symfony\Component\Form\Extension\Core\Type\{IntegerType, TextType};

class PaymentTypes extends BoxTypes
{
    const PAYMENT_TYPE_YANDEX = "yandex";
    const PAYMENT_TYPE_1C = "1c";
    const PAYMENT_TYPE_SBERBANK = "sberbank";

    private static $translationDomain = 'BoxBundle';

    /**
     * Get ArrayForAdmin
     *
     * @return array
     */
    public static function getArrayForAdmin()
    {
        return [
            'Yandex' => [
                'yandex_id' => ['yandex_id', IntegerType::class, [
                    'required' => true,
                    'label' => 'ID Yandex',
                    'translation_domain' => self::$translationDomain,
                ]],
                'secret' => ['secret', TextType::class, [
                    'required' => true,
                    'label' => 'Secret',
                    'translation_domain' => self::$translationDomain,
                ]],
            ],
            'Sberbank' => [
                'sberbank_username' => ['sberbank_username', TextType::class, [
                    'required' => true,
                    'label' => 'Username',
                    'translation_domain' => self::$translationDomain,
                ]],
                'sberbank_password' => ['sberbank_password', TextType::class, [
                    'required' => true,
                    'label' => 'Password',
                    'translation_domain' => self::$translationDomain,
                ]],
                'secret' => ['secret', TextType::class, [
                    'required' => true,
                    'label' => 'Secret',
                    'translation_domain' => self::$translationDomain,
                ]],
            ],
        ];
    }
}