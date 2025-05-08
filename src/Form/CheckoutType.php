<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('token', HiddenType::class, [
                'data' => $options['csrf_token'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email address',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'data' => $options['user_email'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false, 
            'csrf_token' => null,
            'user_email' => null,
        ]);
    }
}