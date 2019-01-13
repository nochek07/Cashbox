<?php

namespace Cashbox\BoxBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class YandexTransactionAdmin extends AbstractAdmin
{
    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('inn')
            ->add('datetime', 'datetime', [
                'format' => 'd.m.Y H:i:s'
            ])
            ->add('action')
            ->add('Sum')
            ->add('customerNumber')
            ->add('email')
        ;
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('edit');
        $collection->remove('delete');
        $collection->remove('create');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('action')
            ->add('customerNumber')
            ->add('datetime')
            ->add('inn')
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
           ->add('id')
           ->add('inn')
           ->add('datetime')
           ->add('action')
           ->add('Sum')
           ->add('customerNumber')
           ->add('email')
           ->add('dataPost');
        ;
    }
}    