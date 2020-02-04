<?php

namespace Cashbox\BoxBundle\Admin\Register;

use Cashbox\BoxBundle\Model\OrganizationModel;
use Cashbox\BoxBundle\Model\Type\{PaymentTypes, OtherTypes, KKMTypes};
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper};
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ReportKKMAdmin extends AbstractAdmin
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
            ->add('typePayment')
            ->add('state')
            ->add('action')
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
            ->add('datetime')
            ->add('typePayment', null, [
                ], 'choice', [
                    'choices' => array_keys(array_merge(PaymentTypes::getArrayForAdmin(), OtherTypes::getArrayForAdmin())),
                    'choice_label' => function($type) {
                        return $type;
                    },
                ]
            )
            ->add('type', null, [
                ], 'choice', [
                    'choices' => array_keys(KKMTypes::getArrayForAdmin()),
                    'choice_label' => function($type) {
                        return $type;
                    },
                ]
            )
            ->add('INN', null, [
                    'label' => 'Organization',
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
            ->add('action')
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
            ->add('action')
            ->add('uuid', null, [
               'label' => 'UUID'
            ])
            ->add('dataKKM', null, [
                'label' => 'Data KKM'
            ])
            ->add('dataPost')
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