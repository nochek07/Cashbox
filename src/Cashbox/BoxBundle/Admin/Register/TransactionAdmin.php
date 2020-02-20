<?php

namespace Cashbox\BoxBundle\Admin\Register;

use Cashbox\BoxBundle\Model\OrganizationModel;
use Cashbox\BoxBundle\Model\Type\PaymentTypes;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper};
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class TransactionAdmin extends AbstractAdmin
{
    protected $translationDomain = 'BoxBundle';
    protected $choicesOrganizations = [];

    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'datetime',
    ];

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $this->setChoiceOrganization();

        $listMapper
            ->addIdentifier('id', null, [
                'label' => 'ID'
            ])
            ->add('datetime', 'datetime', [
                'format' => 'd.m.Y H:i:s'
            ])
            ->add('INN', 'choice', [
                    'label' => 'Organization',
                    'choices' => $this->choicesOrganizations
                ]
            )
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
        $choicesOrganizations = $this->choicesOrganizations;
        $datagridMapper
            ->add('customerNumber')
            ->add('datetime')
            ->add('type', null, [
                    'show_filter' => true,
                ], 'choice', [
                    'choices' => array_keys(PaymentTypes::getArrayForAdmin()),
                    'choice_label' => function($type) {
                        return $type;
                    },
                ]
            )
            ->add('INN', null, [
                    'label' => 'Organization',
                    'show_filter' => true,
                ], 'choice', [
                    'choices' => array_keys($choicesOrganizations),
                    'choice_label' => function($INN) use ($choicesOrganizations) {
                        if (isset($choicesOrganizations[$INN])) {
                            return $choicesOrganizations[$INN]->getName();
                        } else {
                            return $INN;
                        }
                    },
                ]
            )
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

    protected function setChoiceOrganization()
    {
        $this->choicesOrganizations = OrganizationModel::setChoiceOrganization(
            $this->getConfigurationPool()
                ->getContainer()
                ->get('doctrine_mongodb')
        );
    }
}    