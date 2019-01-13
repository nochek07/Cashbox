<?php

namespace Cashbox\BoxBundle\Admin;

use Cashbox\BoxBundle\Models\TaxSystem;
use Cashbox\BoxBundle\Models\Vat;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\Form\Type\BooleanType;

class OrganizationAdmin extends AbstractAdmin
{
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
                        'shop_id' => ['shop_id', 'text', ['required' => true, 'label' => 'Shop ID']],
                        'secret' => ['secret', 'text', ['required' => true, 'label' => 'Secret']],
                        'queue_name' => ['queue_name', 'text', ['required' => true, 'label' => 'Queue name']],
                        'queue_id' => ['queue_id', 'integer', ['required' => true, 'label' => 'Queue ID']],
                        'tax_system' => ['tax_system', 'choice', ['required' => true, 'label' => 'Tax system', 'choices' => TaxSystem::$choices]],
                        'vat' => ['vat', 'choice', ['required' => true, 'label' => 'Vat', 'choices' => Vat::$choices]],
                        'cancel_action' => ['cancel_action', BooleanType::class, ['required' => true, 'label' => 'Cancel action']],
                    ]
                ])
            ->end()
            ->with('Sberbank', ['class' => 'col-md-6'])
                ->add('dataSberbank', 'sonata_type_immutable_array', [
                    'label' => false,
                    'keys' => [
                        'sberbank_username' => ['sberbank_username', 'text', ['required' => true, 'label' => 'Username']],
                        'sberbank_password' => ['sberbank_password', 'text', ['required' => true, 'label' => 'Password']],
                        'secret' => ['secret', 'text', ['required' => true, 'label' => 'Secret']],
                    ]
                ])
            ->end()
            ->with('Yandex', ['class' => 'col-md-6'])
                ->add('dataYandex', 'sonata_type_immutable_array', [
                    'label' => false,
                    'keys' => [
                        'yandex_id' => ['yandex_id', 'integer', ['required' => true, 'label' => 'ID']],
                        'secret' => ['secret', 'text', ['required' => true, 'label' => 'Secret']],
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