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
    protected $choicesActions = [];
    protected $choicesStates = [];

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
        $this->setChoiceAction();
        $this->setChoiceState();

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
            ->add('state', 'choice', [
                'choices' => $this->choicesStates,
            ])
            ->add('action', 'choice', [
                'choices' => $this->choicesActions,
                'template' => 'BoxBundle:Admin/sonataproject/CRUD:error_field.html.twig'
            ])
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
            ->add('action', null, [
                ], 'choice', [
                    'choices' => array_keys($this->choicesActions),
                    'choice_label' => function($type) {
                        return $type;
                    },
            ])
            ->add('state', null, [
                ], 'choice', [
                    'choices' => array_keys($this->choicesStates),
                    'choice_label' => function($type) {
                        return $type;
                    },
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

    /**
     * Set Organizations for Choice
     */
    protected function setChoiceOrganization()
    {
        $this->choicesOrganizations = OrganizationModel::setChoiceOrganization(
            $this->getConfigurationPool()
                ->getContainer()
                ->get('doctrine_mongodb')
        );
    }

    /**
     * Set Actions for Choice
     */
    protected function setChoiceAction()
    {
        $this->choicesActions = [
            KKMTypes::KKM_ACTION_SALE => $this->trans(KKMTypes::KKM_ACTION_SALE, [], 'messages'),
            KKMTypes::KKM_ACTION_REFUND => $this->trans(KKMTypes::KKM_ACTION_REFUND, [], 'messages'),
            KKMTypes::KKM_ACTION_ERROR => $this->trans(KKMTypes::KKM_ACTION_ERROR, [], 'messages')
        ];
    }

    /**
     * Set States for Choice
     */
    protected function setChoiceState()
    {
        $this->choicesStates = [
            KKMTypes::KKM_STATE_NEW => $this->trans(KKMTypes::KKM_STATE_NEW, [], 'messages'),
            KKMTypes::KKM_STATE_ERROR => $this->trans(KKMTypes::KKM_STATE_ERROR, [], 'messages'),
            KKMTypes::KKM_STATE_OTHER_ERROR => $this->trans(KKMTypes::KKM_STATE_OTHER_ERROR, [], 'messages')
        ];
    }
}    