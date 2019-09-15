<?php

namespace Cashbox\BoxBundle\Admin;

use Cashbox\BoxBundle\Model\Type;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class OtherAdmin extends ObjectAbstractAdmin
{
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $params = $this->addImmutableArray($formMapper, new Type\OtherTypes, ['type', 'kkm']);
        $formMapper
            ->add('kkm', 'sonata_type_model', [
                'btn_add' => false,
                'required' => false,
                'choices' => $this->getParentFieldDescription()->getOption('kkms')
            ])
            ->add('type', 'sonata_type_choice_field_mask', [
                'choices' => $params['choices'],
                'map' => $params['map'],
                'required' => true,
                'mapped' => true,
            ])
        ;
        $formMapper->reorder($params['order']);
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