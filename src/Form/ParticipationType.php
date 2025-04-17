<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Participation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
class ParticipationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'attr' => ['class' => 'form-control'],
            'label_attr' => ['class' => 'form-label'],
            'constraints' => [
                new Assert\NotBlank(['message' => 'Email cannot be blank']),
                new Assert\Email(['message' => 'Please enter a valid email address'])
            ],
            'error_bubbling' => false
        ])
        ->add('phoneNumber', TextType::class, [
            'attr' => ['class' => 'form-control'],
            'label_attr' => ['class' => 'form-label'],
            'constraints' => [
                new Assert\NotBlank(['message' => 'Phone number cannot be blank']),
                new Assert\Regex([
                    'pattern' => '/^\+?[0-9]{10,15}$/',
                    'message' => 'Please enter a valid phone number (10-15 digits, + prefix optional)'
                ])
                ],
                'error_bubbling' => false
        ])
        ->add('gender', ChoiceType::class, [
            'choices' => [
                'Male' => 'Male',
                'Female' => 'Female',
                'Other' => 'Other',
            ],
            'attr' => ['class' => 'form-control'],
            'label_attr' => ['class' => 'form-label'],
            'constraints' => [
                new Assert\NotBlank(['message' => 'Please select a gender'])
            ],
            'error_bubbling' => false
        ])
        ->add('dateOfBirth', DateType::class, [
            'widget' => 'single_text',
            'attr' => ['class' => 'form-control'],
            'label_attr' => ['class' => 'form-label'],
            'constraints' => [
                new Assert\NotBlank(['message' => 'Please enter your date of birth']),
                new Assert\LessThanOrEqual([
                    'value' => 'today',
                    'message' => 'Date of birth must be in the past'
                ])
                ],
                'error_bubbling' => false
        ])
        ->add('participantType', ChoiceType::class, [
            'choices' => [
                'Individual' => 'Individual',
                'Group' => 'Group',
            ],
            'attr' => ['class' => 'form-control'],
            'label_attr' => ['class' => 'form-label'],
            'constraints' => [
                new Assert\NotBlank(['message' => 'Please select participant type'])
            ],
            'error_bubbling' => false
        ])
        ->add('numberOfParticipants', IntegerType::class, [
            'attr' => ['class' => 'form-control'],
            'label_attr' => ['class' => 'form-label'],
            'constraints' => [
                new Assert\NotBlank(['message' => 'Please enter number of participants']),
                new Assert\Positive(['message' => 'Number must be greater than zero'])
            ],
            'error_bubbling' => false
        ])
        ->add('termsAccepted', CheckboxType::class, [
            'label' => 'I accept the terms and conditions',
            'attr' => ['class' => 'form-check-input'],
            'label_attr' => ['class' => 'form-check-label'],
            'constraints' => [
                new Assert\IsTrue(['message' => 'You must accept the terms and conditions'])
            ],
            'error_bubbling' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participation::class,
            'validation_groups' => ['Default'], // Make sure this is present

        ]);
    }
}
