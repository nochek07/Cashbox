<?php

namespace Cashbox\BoxBundle\Admin;

use Cashbox\BoxBundle\Model\KKM\KKMTypes;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class KKMAdmin extends AbstractAdmin
{
    protected $translationDomain = 'BoxBundle';

    protected $listModes = [];
    
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $order = ['name', 'type'];
        $choices = [];
        $map = [];
        foreach (KKMTypes::getArrayForAdmin() as $key => $value) {
            $choices[$key] = $key;
            $map[$key] = [$key];
            $order[] = $key;

            $keys = KKMTypes::getNewKeys($value);
            if (0 < sizeof($keys)) {
                $formMapper
                    ->add($key, 'sonata_type_immutable_array', [
                        'mapped' => true,
                        'required' => false,
                        'keys' => $keys,
                    ])
                ;
            }
        }

        $formMapper
            ->add('name', null, [
                'trim' => true
            ])
            ->add('type', 'sonata_type_choice_field_mask', [
                'choices' => $choices,
                'map' => $map,
                'required' => true,
                'mapped' => true,
            ])
        ;
        $formMapper->reorder($order);
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
    }
}    