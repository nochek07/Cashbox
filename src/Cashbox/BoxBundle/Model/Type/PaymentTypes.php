<?php

namespace Cashbox\BoxBundle\Model\Type;

use Symfony\Component\Form\Extension\Core\Type\{IntegerType, TextType};

class PaymentTypes extends AbstractTypes
{
    const PAYMENT_TYPE_YANDEX = "Yandex";
    const PAYMENT_TYPE_SBERBANK = "Sberbank";

    /**
     * {@inheritDoc}
     */
    public static function getArrayForAdmin()
    {
        return [
            self::PAYMENT_TYPE_YANDEX => [
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
            self::PAYMENT_TYPE_SBERBANK => [
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