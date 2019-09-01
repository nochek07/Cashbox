<?php

namespace Cashbox\BoxBundle\Admin;

use Cashbox\BoxBundle\Document\{Organization, KKM, Payment};
use Cashbox\BoxBundle\Model\KKM\KKMTypes;
use Cashbox\BoxBundle\Model\Payment\PaymentTypes;
use Doctrine\Common\Collections\ArrayCollection;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Validator\ErrorElement;

class OrganizationAdmin extends AbstractAdmin
{
    protected $translationDomain = 'BoxBundle';

    protected $listModes = [];
    
    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('Basic')
                ->with('Basic', ['label' => false, 'class' => 'col-md-6'])
                    ->add('name')
                    ->add('INN', null, [
                        'label' => 'INN'
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

            ->tab('KKMs')
                ->with('KKMs', ['label' => false, 'class' => 'col-md-6'])
                    ->add('KKMs', 'sonata_type_collection', [
                        'label' => false,
                        'by_reference' => true,
                    ], [
                        'edit' => 'inline',
//                        'inline' => 'table',
                        'admin_code' => 'admin.kkm',
                        'template' => 'BoxBundle:Admin/sonataproject/Form:form_admin_fields.html.twig',
                    ])
                ->end()
            ->end()

            ->tab('Payments')
                ->with('Payments', ['label' => false, 'class' => 'col-md-12'])
                    ->add('payments', 'sonata_type_collection', [
                        'label' => false,
                        'by_reference' => true,
                    ], [
                        'edit' => 'inline',
//                        'inline' => 'table',
                        'admin_code' => 'admin.payment',
                        'template' => 'BoxBundle:Admin/sonataproject/Form:form_admin_fields.html.twig',
                        'kkms' => $this->getSubject()->getKKMs()->toArray()
                    ])
                ->end()
            ->end()
        ;
    }

    /**
     * @param ErrorElement $errorElement
     * @param Organization $organization
     * @return bool
     */
    public function validate(ErrorElement $errorElement, $organization)
    {
        if ($organization->getPayments()->count() > sizeof(PaymentTypes::getArrayForAdmin())) {
            $errorElement
                ->with('payments')
                ->addViolation("Не должно быть больше одной плптежной системы каждого типа")
                ->end()
            ;
            return true;
        }

        $translator = $this->getConfigurationPool()->getContainer()->get('translator');

        $kkmsByPayment = new ArrayCollection();

        $index = 0;
        /**
         * @var Payment $payment
         */
        foreach ($organization->getPayments() as $payment) {
            $kkm = $payment->getKkm();
            if ($kkm instanceof KKM && !$kkmsByPayment->contains($kkm)) {
                $kkmsByPayment->add($kkm);
            }
            if (is_array($payment->getData())) {
                $index++;
                $type = $payment->getType();
                $textError = PaymentTypes::getTextValidation($type, $payment->getData(), $translator);
                if (!empty($textError)) {
                    $errorElement
                        ->with('payments')
                        ->addViolation("Платежная система №{$index} \"{$type}\":<br>" . $textError)
                        ->end()
                    ;
                }
            }
        }

        /**
         * @var KKM $kkm
         */
        foreach ($organization->getKKMs() as $kkm) {
            $kkmsByPayment->removeElement($kkm);
        }
        if ($kkmsByPayment->count() > 0) {
            foreach ($kkmsByPayment as $kkm) {
                $errorElement
                    ->with('KKMs')
                    ->addViolation("Невозможно удалить ККМ: \"{$kkm->getName()}\" ({$kkm->getType()})")
                    ->end();
            }
            return true;
        }

        $index = 0;
        /**
         * @var KKM $kkm
         */
        foreach ($organization->getKKMs() as $kkm) {
            $type = $kkm->getType();
            $additional = $kkm->getAdditional();
            if (isset($additional[$type])) {
                $kkm->setData($additional[$type]);
            }

            if (is_array($kkm->getData())) {
                $index++;
                $type = $kkm->getType();
                $textError = KKMTypes::getTextValidation($type, $kkm->getData(), $translator);
                if (!empty($textError)) {
                    $errorElement
                        ->with('KKMs')
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
        /**
         * @var Payment $payment
         */
        foreach ($organization->getPayments() as $payment) {
            $type = $payment->getType();
            $additional = $payment->getAdditional();
            if (isset($additional[$type])) {
                $payment->setData($additional[$type]);
            }
        }

        /**
         * @var KKM $kkm
         */
        foreach ($organization->getKKMs() as $kkm) {
            $type = $kkm->getType();
            $additional = $kkm->getAdditional();
            if (isset($additional[$type])) {
                $kkm->setData($additional[$type]);
            }
        }
    }

    /**
     * @param Organization $organization
     */
    public function prePersist($organization)
    {
        $this->preUpdate($organization);
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
            ->addIdentifier('INN', null, [
                'route' => ['name' => 'edit'],
                'label' => 'INN',
                'editable' => true
            ])
        ;
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
//        $collection->remove('edit');
        $collection->remove('delete');
//        $collection->remove('create');
    }

    /**
     * @return mixed
     */
    public function getNewInstance()
    {
        $instance = parent::getNewInstance();
        $instance->setPatternNomenclature('Товар по счету №%s');
        return $instance;
    }

    public function getFormTheme()
    {
        return array_merge(
            parent::getFormTheme(),
            ['BoxBundle:Admin/sonataproject/Form:form_admin_fields.html.twig']
        );
    }
}    