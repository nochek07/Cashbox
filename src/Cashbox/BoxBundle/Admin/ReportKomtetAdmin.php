<?php

namespace Cashbox\BoxBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper};
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ReportKomtetAdmin extends AbstractAdmin
{
    protected $translationDomain = 'BoxBundle';

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, [
                'label' => 'ID'
            ])
            ->add('datetime', 'datetime', [
                'format' => 'd.m.Y H:i:s'
            ])
            ->add('inn', null, [
                'label' => 'INN'
            ])
            ->add('action')
            ->add('type')
            ->add('state')
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

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('action')
            ->add('datetime')
            ->add('type')
            ->add('inn', null, [
                'label' => 'INN'
            ])
            ->add('state')
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
           ->add('id', null, [
               'label' => 'ID'
           ])
           ->add('datetime', 'datetime', [
               'format' => 'd.m.Y H:i:s'
           ])
           ->add('inn', null, [
               'label' => 'INN'
           ])
           ->add('action')
           ->add('type')
           ->add('state')
           ->add('uuid', null, [
               'label' => 'UUID'
           ])
           ->add('dataKomtet')
           ->add('dataPost')
        ;
    }
}    