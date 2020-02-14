<?php

namespace Cashbox\BoxBundle\Model\Type;

use Cashbox\BoxBundle\Model\KKM;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, IntegerType, TextType};

class KKMTypes extends AbstractTypes
{
    const KKM_TYPE_KOMTET = "Komtet";

    const KKM_ACTION_SALE = "sale";
    const KKM_ACTION_REFUND = "refund";
    const KKM_ACTION_ERROR = "error";

    const KKM_STATE_NEW = 'new';
    const KKM_STATE_ERROR = 'error';
    const KKM_STATE_OTHER_ERROR = 'otherError';

    /**
     * @var array
     */
    public static $arrayKkmModelClass = [
        self::KKM_TYPE_KOMTET => KKM\Komtet::class,
    ];

    /**
     * {@inheritDoc}
     */
    public static function getArrayForAdmin()
    {
        return [
            self::KKM_TYPE_KOMTET => [
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
                    'choices' => KKM\TaxSystem::$choices,
                    'translation_domain' => self::$translationDomain,
                ]],
                'vat' => ['vat', ChoiceType::class, [
                    'required' => true,
                    'label' => 'Vat',
                    'choices' => KKM\Vat::$choices,
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