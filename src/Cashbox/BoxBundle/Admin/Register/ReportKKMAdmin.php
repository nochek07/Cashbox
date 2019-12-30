<?php

namespace Cashbox\BoxBundle\Admin\Register;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper};
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ReportKKMAdmin extends AbstractAdmin
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
            ->add('typePayment')
            ->add('INN', null, [
                'label' => 'INN'
            ])
            ->add('state')
            ->add('type')
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
            ->add('datetime')
            ->add('typePayment')
            ->add('type')
            ->add('INN', null, [
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
            ->add('typePayment')
            ->add('type')
            ->add('INN', null, [
               'label' => 'INN'
            ])
            ->add('state')
            ->add('uuid', null, [
               'label' => 'UUID'
            ])
            ->add('dataKKM', null, [
                'label' => 'Data KKM'
            ])
            ->add('dataPost')
        ;
    }
}    