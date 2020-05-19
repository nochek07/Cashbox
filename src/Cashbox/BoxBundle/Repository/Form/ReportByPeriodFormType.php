<?php

namespace Cashbox\BoxBundle\Repository\Form;

use Cashbox\BoxBundle\Document\Organization;
use Cashbox\BoxBundle\Form\ReportByPeriodForm;
use Symfony\Component\Form\{AbstractType, FormBuilderInterface};
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, DateType, SubmitType};
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportByPeriodFormType extends AbstractType
{
    const ALL_ORGANIZATION = 'All';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choicesOrganizations = $options['organization'];
        $keyChoicesOrganizations = array_keys($choicesOrganizations);
        array_unshift($keyChoicesOrganizations, self::ALL_ORGANIZATION);
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
            ->add('INN', ChoiceType::class, [
                'choices' => $keyChoicesOrganizations,
                'choice_label' => function ($inn) use ($choicesOrganizations) {
                    if (isset($choicesOrganizations[$inn]) && $choicesOrganizations[$inn] instanceof Organization) {
                        return $choicesOrganizations[$inn]->getName();
                    } else {
                        return $inn;
                    }
                },
                'attr' => [
                    'class' => 'form-control',
                ],
                'label' => 'Organization'
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
            'organization' => [],
        ]);
    }
}