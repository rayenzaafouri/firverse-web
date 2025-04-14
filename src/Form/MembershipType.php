<?php

namespace App\Form;

use App\Entity\Gym;
use App\Entity\Membership;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MembershipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start_date', null, [
                'widget' => 'single_text',
            ])
            ->add('end_date', null, [
                'widget' => 'single_text',
            ])
            ->add('price', NumberType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'step' => 5,
                    'min' => 0
                ]
            ])
            ->add('coaching', ChoiceType::class, [
                'choices'  => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'placeholder' => 'Sélectionner',
                'attr' => ['class' => 'form-control']
            ])

            ->add('status', ChoiceType::class, [
                'choices'  => [
                    'Active' => 'Active',
                    'Inactive' => 'Inactive',
                    'Expired' => 'Expired',
                ],
                'placeholder' => 'Choisir un statut',
            ])
            ->add('gym', EntityType::class, [
                'class' => Gym::class,
                'choice_label' => 'id',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Membership::class,
        ]);
    }
}
