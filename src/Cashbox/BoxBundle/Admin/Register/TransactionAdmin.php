<?php

namespace Cashbox\BoxBundle\Admin\Register;

use Cashbox\BoxBundle\Model\OrganizationModel;
use Cashbox\BoxBundle\Model\Type\PaymentTypes;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper};
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TransactionAdmin extends AbstractAdmin
{
    protected $translationDomain = 'BoxBundle';
    protected $choiceOrganizations = [];

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
            ->add('tin', 'choice', [
                    'label' => 'Organization',
                    'choices' => $this->choiceOrganizations
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
        $choiceOrganizations = $this->choiceOrganizations;
        $datagridMapper
            ->add('customerNumber')
            ->add('datetime')
            ->add('type', null, [
                    'show_filter' => true,
                ], ChoiceType::class, [
                    'choices' => array_keys(PaymentTypes::getArrayForAdmin()),
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
            ->add('tin', null, [
               'label' => 'Tin'
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
        $this->choiceOrganizations = OrganizationModel::setChoiceOrganization(
            $this->getConfigurationPool()
                ->getContainer()
                ->get('doctrine_mongodb')
        );
    }
}    