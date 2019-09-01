<?php

namespace Cashbox\BoxBundle\Model\KKM;

use Cashbox\BoxBundle\Model\BoxTypes;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, IntegerType, TextType};

class KKMTypes extends BoxTypes
{
    private static $translationDomain = 'BoxBundle';

    /**
     * @var array $arrayKkmModelClass
     */
    public static $arrayKkmModelClass = [
        'Komtet' => Komtet::class,
    ];

    /**
     * Get ArrayForAdmin
     *
     * @return array
     */
    public static function getArrayForAdmin()
    {
        return [
            'Komtet' => [
                'shop_id' => ['shop_id', TextType::class, [
                    'required' => true,
                    'label' => 'Shop ID',
                    'translation_domain' => self::$translationDomain
                ]],
                'secret' => ['secret', TextType::class, [
                    'required' => true,
                    'label' => 'Secret',
                    'translation_domain' => self::$translationDomain
                ]],
                'queue_name' => ['queue_name', TextType::class, [
                    'required' => true,
                    'label' => 'Queue name',
                    'translation_domain' => self::$translationDomain
                ]],
                'queue_id' => ['queue_id', IntegerType::class, [
                    'required' => true,
                    'label' => 'Queue ID',
                    'translation_domain' => self::$translationDomain
                ]],
                'tax_system' => ['tax_system', ChoiceType::class, [
                    'required' => true,
                    'label' => 'Tax system',
                    'choices' => TaxSystem::$choices,
                    'translation_domain' => self::$translationDomain,
                ]],
                'vat' => ['vat', ChoiceType::class, [
                    'required' => true,
                    'label' => 'Vat',
                    'choices' => Vat::$choices,
                    'translation_domain' => self::$translationDomain
                ]],
                'cancel_action' => ['cancel_action', BooleanType::class, [
                    'required' => true,
                    'label' => 'Cancel action',
                    'translation_domain' => self::$translationDomain
                ]],
            ],
        ];
    }
}