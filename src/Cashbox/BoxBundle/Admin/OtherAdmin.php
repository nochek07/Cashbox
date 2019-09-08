<?php

namespace Cashbox\BoxBundle\Admin;

use Cashbox\BoxBundle\Model\Payment\OtherTypes;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class OtherAdmin extends AbstractAdmin
{
    protected $translationDomain = 'BoxBundle';

    protected $listModes = [];
    
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $order = ['type', 'kkm'];
        $choices = [];
        $map = [];
        foreach (OtherTypes::getArrayForAdmin() as $key => $value) {
            $choices[$key] = $key;
            $map[$key] = [$key];
            $order[] = $key;

            $keys = OtherTypes::getNewKeys($value);
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

        $this->getParent();

        $formMapper
            ->add('kkm', 'sonata_type_model', [
                'btn_add' => false,
                'required' => false,
                'choices' => $this->getParentFieldDescription()->getOption('kkms')
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