<?php

namespace Cashbox\BoxBundle\Model\Type;

use Cashbox\BoxBundle\Model\Till;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, IntegerType, TextType};

class TillTypes extends AbstractTypes
{
    const TILL_TYPE_KOMTET = "Komtet";

    const TILL_ACTION_SALE = "sale";
    const TILL_ACTION_REFUND = "refund";
    const TILL_ACTION_ERROR = "error";

    const TILL_STATE_NEW = 'new';
    const TILL_STATE_ERROR = 'error';
    const TILL_STATE_OTHER_ERROR = 'otherError';

    /**
     * @var array
     */
    public static $arrayTillModelClass = [
        self::TILL_TYPE_KOMTET => Till\Komtet::class,
    ];

    /**
     * {@inheritDoc}
     */
    public static function getArrayForAdmin(): array
    {
        return [
            self::TILL_TYPE_KOMTET => [
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
                    'choices' => Till\TaxSystem::$choices,
                    'translation_domain' => self::$translationDomain,
                ]],
                'vat' => ['vat', ChoiceType::class, [
                    'required' => true,
                    'label' => 'Vat',
                    'choices' => Till\Vat::$choices,
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