<?php

namespace Cashbox\BoxBundle\Admin;

use Cashbox\BoxBundle\Model\Type;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\{ChoiceFieldMaskType, ModelType};
use Sonata\AdminBundle\Route\RouteCollection;

class PaymentAdmin extends AbstractObjectAdmin
{
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $params = $this->addImmutableArray($formMapper, new Type\PaymentTypes, ['type', 'kkm']);
        $formMapper
            ->add('kkm', ModelType::class, [
                'btn_add' => false,
                'required' => false,
                'choices' => $this->getParentFieldDescription()->getOption('kkms')
            ])
            ->add('type', ChoiceFieldMaskType::class, [
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