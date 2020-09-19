<?php

namespace Cashbox\BoxBundle\Admin;

use Cashbox\BoxBundle\Document\{AbstractObjectDocument, Organization, Other, Payment, Till};
use Cashbox\BoxBundle\Model\Type;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Validator\ErrorElement;

class OrganizationAdmin extends AbstractAdmin
{
    protected $translationDomain = 'BoxBundle';
    protected $listModes = [];
    
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $tills = $this->getSubject()
            ->getTills()->toArray();

        $formMapper
            ->tab('Basic')
                ->with('Basic', ['label' => false, 'class' => 'col-md-6'])
                    ->add('name')
                    ->add('tin', null, [
                        'label' => 'Tin'
                    ])
                    ->add('patternNomenclature')
                        ->addHelp('patternNomenclature', 'Строка в чеке')
                    ->add('secret')
                        ->addHelp('secret', 'Для плагинов')
                    ->add('adminEmail', null, [
                        'required' => false
                    ])
                        ->addHelp('adminEmail', 'Уведомление о чеках')
                ->end()
            ->end()

            ->tab('Tills')
                ->with('Tills', ['label' => false, 'class' => 'box-tabs col-md-6'])
                    ->add('tills', CollectionType::class, [
                        'label' => false,
                        'by_reference' => true,
                    ], [
                        'edit' => 'inline',
                        //'inline' => 'table',
                        'admin_code' => 'admin.till',
                    ])
                ->end()
            ->end()
            
            ->tab('Payments')
                ->with('Payments', ['label' => false, 'class' => 'box-tabs col-md-6'])
                    ->add('payments', CollectionType::class, [
                        'label' => false,
                        'by_reference' => true,
                    ], [
                        'edit' => 'inline',
                        //'inline' => 'table',
                        'admin_code' => 'admin.payment',
                        'tills' => $tills
                    ])
                ->end()
            ->end()

            ->tab('Others')
                ->with('Others', ['label' => false, 'class' => 'box-tabs col-md-6'])
                    ->add('others', CollectionType::class, [
                        'label' => false,
                        'by_reference' => true,
                    ], [
                        'edit' => 'inline',
                        //'inline' => 'table',
                        'admin_code' => 'admin.other',
                        'tills' => $tills
                    ])
                ->end()
            ->end()
        ;
    }

    /**
     * Validate
     *
     * @param ErrorElement $errorElement
     * @param Organization $organization
     *
     * @return bool
     */
    public function validate(ErrorElement $errorElement, $organization)
    {
        if ($organization->getPayments()->count() > sizeof(Type\PaymentTypes::getArrayForAdmin())) {
            $errorElement
                ->with('payments')
                ->addViolation("Платежные системы не должны повторяться по типам")
                ->end()
            ;
            return true;
        }

        if ($organization->getOthers()->count() > sizeof(Type\OtherTypes::getArrayForAdmin())) {
            $errorElement
                ->with('others')
                ->addViolation("Другие системы не должны повторяться по типам")
                ->end()
            ;
            return true;
        }

        $this->preUpdate($organization);

        $translator = $this->getConfigurationPool()->getContainer()->get('translator');

        $paymentTills = new ArrayCollection();

        $index = 0;
        /**
         * @var Payment $payment
         */
        foreach ($organization->getPayments() as $payment) {
            $till = $payment->getTill();
            if ($till instanceof Till && !$paymentTills->contains($till)) {
                $paymentTills->add($till);
            }
            if (is_array($payment->getData())) {
                $index++;
                $type = $payment->getType();
                $textError = Type\PaymentTypes::getTextValidation($type, $payment->getData(), $translator);
                if (!empty($textError)) {
                    $errorElement
                        ->with('payments')
                        ->addViolation("Платежная система №{$index} \"{$type}\":<br>" . $textError)
                        ->end()
                    ;
                }
            }
        }

        $index = 0;
        /**
         * @var Other $other
         */
        foreach ($organization->getOthers() as $other) {
            $till = $other->getTill();
            if ($till instanceof Till && !$paymentTills->contains($till)) {
                $paymentTills->add($till);
            }
            if (is_array($other->getData())) {
                $index++;
                $type = $other->getType();
                $textError = Type\OtherTypes::getTextValidation($type, $other->getData(), $translator);
                if (!empty($textError)) {
                    $errorElement
                        ->with('others')
                        ->addViolation("Другая система №{$index} \"{$type}\":<br>" . $textError)
                        ->end()
                    ;
                }
            }
        }

        /**
         * @var Till $till
         */
        foreach ($organization->getTills() as $till) {
            $paymentTills->removeElement($till);
        }
        if ($paymentTills->count() > 0) {
            foreach ($paymentTills as $till) {
                $errorElement
                    ->with('Tills')
                    ->addViolation("Невозможно удалить ККМ: \"{$till->getName()}\" ({$till->getType()})")
                    ->end()
                ;
            }
            return true;
        }

        $index = 0;
        /**
         * @var Till $till
         */
        foreach ($organization->getTills() as $till) {
            if (is_array($till->getData())) {
                $index++;
                $type = $till->getType();
                $textError = Type\TillTypes::getTextValidation($type, $till->getData(), $translator);
                if (!empty($textError)) {
                    $errorElement
                        ->with('Tills')
                        ->addViolation("ККМ №{$index} \"{$type}\":<br>" . $textError)
                        ->end()
                    ;
                }
            }
        }

        return true;
    }

    /**
     * @param Organization $organization
     */
    public function preUpdate($organization)
    {
        $this->setDataByAdditional($organization->getPayments());
        $this->setDataByAdditional($organization->getOthers());
        $this->setDataByAdditional($organization->getTills());
    }

    /**
     * @param Organization $organization
     */
    public function prePersist($organization)
    {
        $this->preUpdate($organization);
    }

    /**
     * @param Collection $objects
     */
    private function setDataByAdditional($objects)
    {
        /**
         * @var AbstractObjectDocument $object
         */
        foreach ($objects as $object) {
            $type = $object->getType();
            $additional = $object->getAdditional();
            if (isset($additional[$type])) {
                $object->setData($additional[$type]);
            }
        }
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, [
                'route' => ['name' => 'edit'],
                'label' => 'ID',
                'editable' => true
            ])
            ->addIdentifier('name', null, [
                'route' => ['name' => 'edit'],
                'editable' => true
            ])
            ->addIdentifier('tin', null, [
                'route' => ['name' => 'edit'],
                'label' => 'Tin',
                'editable' => true
            ])
        ;
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        //$collection->remove('edit');
        //$collection->remove('delete');
        //$collection->remove('create');
    }

    /**
     * {@inheritDoc}
     */
    public function getNewInstance()
    {
        $instance = parent::getNewInstance();
        $instance->setPatternNomenclature('Товар по счету №%s');
        return $instance;
    }

    /**
     * @return array
     */
    public function getFormTheme()
    {
        return array_merge(
            parent::getFormTheme(),
            ['BoxBundle:Admin/sonataproject/Form:form_admin_fields.html.twig']
        );
    }
}    