<?php

namespace App\Form;

use App\Entity\Gym;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class GymType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('adresse')
             ->add('longitude', NumberType::class, [
                        'required' => false,
                        'attr' => ['step' => 'any'],
                    ])
                    ->add('latitude', NumberType::class, [
                        'required' => false,
                        'attr' => ['step' => 'any'],
                    ])
            ->add('phone')
            ->add('email')
            ->add('capacity')
            ->add('opening_hours')
            ->add('rating', ChoiceType::class, [
                   'choices' => [
                       '1' => 1,
                       '2' => 2,
                       '3' => 3,
                       '4' => 4,
                       '5' => 5,
                   ],
                   'placeholder' => 'Select a rating',
                   'attr' => ['class' => 'form-control'],
                   'required' => false,
               ])
              ->add('createdAt', DateTimeType::class, [
                  'widget' => 'single_text',
                  'required' => true,
                  'constraints' => [
                      new NotBlank([
                          'message' => 'La date de création est obligatoire.',
                      ]),
                  ],
                  'attr' => ['class' => 'form-control'],
              ])
            ->add('image', TextType::class, [
                'label' => 'Image URL',
                'required' => false,
            ])
            ->add('partner', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Gym::class,
        ]);
    }
}
