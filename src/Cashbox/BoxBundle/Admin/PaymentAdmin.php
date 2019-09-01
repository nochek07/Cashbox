<?php

namespace Cashbox\BoxBundle\Admin;

use Cashbox\BoxBundle\Model\Payment\PaymentTypes;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class PaymentAdmin extends AbstractAdmin
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
        foreach (PaymentTypes::getArrayForAdmin() as $key => $value) {
            $choices[$key] = $key;
            $map[$key] = [$key];
            $order[] = $key;

            $formMapper
                ->add($key, 'sonata_type_immutable_array', [
                    'mapped' => true,
                    'required' => false,
                    'keys' => PaymentTypes::getNewKeys($value),
                ])
            ;
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