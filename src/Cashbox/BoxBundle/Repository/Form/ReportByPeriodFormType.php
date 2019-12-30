<?php

namespace Cashbox\BoxBundle\Repository\Form;

use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Cashbox\BoxBundle\Form\ReportByPeriodForm;
use Symfony\Component\Form\Extension\Core\Type\{DateType, SubmitType};
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportByPeriodFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateStart', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('dateEnd', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary'
                ],
                'label' => 'Do'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReportByPeriodForm::class,
            'translation_domain' => 'ReportsAdmin',
        ]);
    }
}