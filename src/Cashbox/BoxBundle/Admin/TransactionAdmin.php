<?php

namespace Cashbox\BoxBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper};
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class TransactionAdmin extends AbstractAdmin
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
            ->add('INN', null, [
                'label' => 'INN'
            ])
            ->add('Sum')
            ->add('action')
            ->add('customerNumber')
            ->add('email')
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
            ->add('customerNumber')
            ->add('datetime')
            ->add('type')
            ->add('INN', null, [
                'label' => 'INN'
            ])
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
            ->add('type')
            ->add('datetime', 'datetime', [
               'format' => 'd.m.Y H:i:s'
            ])
            ->add('INN', null, [
               'label' => 'INN'
            ])
            ->add('Sum')
            ->add('action')
            ->add('customerNumber')
            ->add('email')
            ->add('dataPost', null, ['template' => 'BoxBundle:MongoDB:show_hash.html.twig'])
        ;
    }
}    