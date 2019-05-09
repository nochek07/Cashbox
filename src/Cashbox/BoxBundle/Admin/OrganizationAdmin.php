<?php

namespace Cashbox\BoxBundle\Admin;

use Cashbox\BoxBundle\Model\{TaxSystem, Vat};
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, IntegerType, TextType};

class OrganizationAdmin extends AbstractAdmin
{
    protected $translationDomain = 'BoxBundle';

    protected $listModes = [];
    
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Basic', ['class' => 'col-md-6'])
                ->add('name')
                ->add('INN', null, [
                    'label' => 'INN'
                ])
                ->add('patternNomenclature')
                    ->addHelp('patternNomenclature', 'Строка в чеке')
                ->add('secret')
                    ->addHelp('secret', 'Для плагинов')
                ->add('adminEmail', null, [
                    'required' => false
                ])
                    ->addHelp('adminEmail', 'Для писем об отправленных чеках')
            ->end()
            ->with('Komtet', ['class' => 'col-md-6'])
                ->add('dataKomtet', 'sonata_type_immutable_array', [
                    'label' => false,
                    'keys' => [
                        'shop_id' => ['shop_id', TextType::class, [
                            'required' => true,
                            'label' => 'Shop ID',
                            'translation_domain' => $this->translationDomain
                        ]],
                        'secret' => ['secret', TextType::class, [
                            'required' => true,
                            'label' => 'Secret',
                            'translation_domain' => $this->translationDomain
                        ]],
                        'queue_name' => ['queue_name', TextType::class, [
                            'required' => true,
                            'label' => 'Queue name',
                            'translation_domain' => $this->translationDomain
                        ]],
                        'queue_id' => ['queue_id', IntegerType::class, [
                            'required' => true,
                            'label' => 'Queue ID',
                            'translation_domain' => $this->translationDomain
                        ]],
                        'tax_system' => ['tax_system', ChoiceType::class, [
                            'required' => true,
                            'label' => 'Tax system',
                            'choices' => TaxSystem::$choices,
                            'translation_domain' => $this->translationDomain
                        ]],
                        'vat' => ['vat', ChoiceType::class, [
                            'required' => true,
                            'label' => 'Vat',
                            'choices' => Vat::$choices,
                            'translation_domain' => $this->translationDomain
                        ]],
                        'cancel_action' => ['cancel_action', BooleanType::class, [
                            'required' => true,
                            'label' => 'Cancel action',
                            'translation_domain' => $this->translationDomain
                        ]],
                    ]
                ])
            ->end()
            ->with('Sberbank', ['class' => 'col-md-6'])
                ->add('dataSberbank', 'sonata_type_immutable_array', [
                    'label' => false,
                    'keys' => [
                        'sberbank_username' => ['sberbank_username', TextType::class, [
                            'required' => true,
                            'label' => 'Username',
                            'translation_domain' => $this->translationDomain
                        ]],
                        'sberbank_password' => ['sberbank_password', TextType::class, [
                            'required' => true,
                            'label' => 'Password',
                            'translation_domain' => $this->translationDomain
                        ]],
                        'secret' => ['secret', TextType::class, [
                            'required' => true,
                            'label' => 'Secret',
                            'translation_domain' => $this->translationDomain
                        ]],
                    ]
                ])
            ->end()
            ->with('Yandex', ['class' => 'col-md-6'])
                ->add('dataYandex', 'sonata_type_immutable_array', [
                    'label' => false,
                    'keys' => [
                        'yandex_id' => ['yandex_id', IntegerType::class, [
                            'required' => true,
                            'label' => 'ID Yandex',
                            'translation_domain' => $this->translationDomain
                        ]],
                        'secret' => ['secret', TextType::class, [
                            'required' => true,
                            'label' => 'Secret',
                            'translation_domain' => $this->translationDomain
                        ]],
                    ]
                ])
            ->end()
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, [
                'route' => ['name' => 'edit'],
                'label' => 'ID',
                'editable' => true
            ])
            ->addIdentifier('name', null, [
                'route' => ['name' => 'edit'],
                'editable' => true
            ])
            ->addIdentifier('INN', null, [
                'route' => ['name' => 'edit'],
                'label' => 'INN',
                'editable' => true
            ])
        ;
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        //$collection->remove('edit');
        $collection->remove('delete');
        //$collection->remove('create');
    }

    /**
     * @return mixed
     */
    public function getNewInstance()
    {
        $instance = parent::getNewInstance();
        $instance->setPatternNomenclature('Товар по счету №%s');

        return $instance;
    }
}    