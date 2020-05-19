<?php

namespace Cashbox\BoxBundle\Admin;

use Cashbox\BoxBundle\Document\{AbstractObjectDocument, KKM, Organization, Other, Payment};
use Cashbox\BoxBundle\Model\Type;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
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
        $kkms = $this->getSubject()
            ->getKKMs()->toArray();

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
                ->with('KKMs', ['label' => false, 'class' => 'box-tabs col-md-6'])
                    ->add('KKMs', 'sonata_type_collection', [
                        'label' => false,
                        'by_reference' => true,
                    ], [
                        'edit' => 'inline',
                        //'inline' => 'table',
                        'admin_code' => 'admin.kkm',
                    ])
                ->end()
            ->end()

            ->tab('Payments')
                ->with('Payments', ['label' => false, 'class' => 'box-tabs col-md-6'])
                    ->add('payments', 'sonata_type_collection', [
                        'label' => false,
                        'by_reference' => true,
                    ], [
                        'edit' => 'inline',
                        //'inline' => 'table',
                        'admin_code' => 'admin.payment',
                        'kkms' => $kkms
                    ])
                ->end()
            ->end()

            ->tab('Others')
                ->with('Others', ['label' => false, 'class' => 'box-tabs col-md-6'])
                    ->add('others', 'sonata_type_collection', [
                        'label' => false,
                        'by_reference' => true,
                    ], [
                        'edit' => 'inline',
                        //'inline' => 'table',
                        'admin_code' => 'admin.other',
                        'kkms' => $kkms
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
            $kkm = $other->getKkm();
            if ($kkm instanceof KKM && !$kkmsByPayment->contains($kkm)) {
                $kkmsByPayment->add($kkm);
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
                    ->end()
                ;
            }
            return true;
        }

        $index = 0;
        /**
         * @var KKM $kkm
         */
        foreach ($organization->getKKMs() as $kkm) {
            if (is_array($kkm->getData())) {
                $index++;
                $type = $kkm->getType();
                $textError = Type\KKMTypes::getTextValidation($type, $kkm->getData(), $translator);
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
        $this->setDataByAdditional($organization->getPayments());
        $this->setDataByAdditional($organization->getOthers());
        $this->setDataByAdditional($organization->getKKMs());
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