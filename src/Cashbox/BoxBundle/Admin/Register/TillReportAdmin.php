<?php

namespace Cashbox\BoxBundle\Admin\Register;

use Cashbox\BoxBundle\Model\OrganizationModel;
use Cashbox\BoxBundle\Model\Type\{OtherTypes, PaymentTypes, TillTypes};
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper};
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TillReportAdmin extends AbstractAdmin
{
    protected $translationDomain = 'BoxBundle';
    protected $choiceOrganizations = [];
    protected $choiceActions = [];
    protected $choiceStates = [];

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
            ->add('tin', 'choice', [
                    'label' => 'Organization',
                    'choices' => $this->choiceOrganizations,
                ]
            )
            ->add('typePayment')
            ->add('state', 'choice', [
                'choices' => $this->choiceStates,
            ])
            ->add('action', 'choice', [
                'choices' => $this->choiceActions,
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
        $choiceOrganizations = $this->choiceOrganizations;
        $datagridMapper
            ->add('datetime')
            ->add('typePayment', null, [
                    'show_filter' => true,
                ], ChoiceType::class, [
                    'choices' => array_keys(
                        array_merge(PaymentTypes::getArrayForAdmin(), OtherTypes::getArrayForAdmin())
                    ),
                    'choice_label' => function ($type) {
                        return $type;
                    },
                ]
            )
            ->add('type', null, [
                    'show_filter' => true,
                ], ChoiceType::class, [
                    'choices' => array_keys(TillTypes::getArrayForAdmin()),
                    'choice_label' => function ($type) {
                        return $type;
                    },
                ]
            )
            ->add('tin', null, [
                    'label' => 'Organization',
                    'show_filter' => true,
                ], ChoiceType::class, [
                    'choices' => array_map(function ($value) {return (string)$value;}, array_keys($choiceOrganizations)),
                    'choice_label' => function ($tin) use ($choiceOrganizations) {
                        if (isset($choiceOrganizations[$tin])) {
                            return $choiceOrganizations[$tin]->getName();
                        } else {
                            return $tin;
                        }
                    },
                ]
            )
            ->add('action', null, [
                ], ChoiceType::class, [
                    'choices' => array_keys($this->choiceActions),
                    'choice_label' => function ($type) {
                        return $type;
                    },
            ])
            ->add('state', null, [
                ], ChoiceType::class, [
                    'choices' => array_keys($this->choiceStates),
                    'choice_label' => function ($type) {
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
            ->add('tin', null, [
               'label' => 'Tin'
            ])
            ->add('state')
            ->add('action')
            ->add('uuid', null, [
               'label' => 'UUID'
            ])
            ->add('dataTill', null, [
                'label' => 'Data Till'
            ])
            ->add('dataPost')
        ;
    }

    /**
     * Set Organizations for Choice
     */
    protected function setChoiceOrganization()
    {
        $this->choiceOrganizations = OrganizationModel::setChoiceOrganization(
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
        $this->choiceActions = [
            TillTypes::TILL_ACTION_SALE => $this->trans(TillTypes::TILL_ACTION_SALE, [], 'messages'),
            TillTypes::TILL_ACTION_REFUND => $this->trans(TillTypes::TILL_ACTION_REFUND, [], 'messages'),
            TillTypes::TILL_ACTION_ERROR => $this->trans(TillTypes::TILL_ACTION_ERROR, [], 'messages')
        ];
    }

    /**
     * Set States for Choice
     */
    protected function setChoiceState()
    {
        $this->choiceStates = [
            TillTypes::TILL_STATE_NEW => $this->trans(TillTypes::TILL_STATE_NEW, [], 'messages'),
            TillTypes::TILL_STATE_ERROR => $this->trans(TillTypes::TILL_STATE_ERROR, [], 'messages'),
            TillTypes::TILL_STATE_OTHER_ERROR => $this->trans(TillTypes::TILL_STATE_OTHER_ERROR, [], 'messages')
        ];
    }
}    