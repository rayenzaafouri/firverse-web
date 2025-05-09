<?php

namespace App\Form;

use App\Entity\Food;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FoodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('measure', TextType::class)
            ->add('grams', NumberType::class, [
                'scale' => 2,
                'required' => false
            ])
            ->add('calories', NumberType::class, [
                'scale' => 2,
                'required' => false
            ])
            ->add('protein', NumberType::class, [
                'scale' => 2,
                'required' => false
            ])
            ->add('fats', NumberType::class, [
                'scale' => 2,
                'required' => false
            ])
            ->add('saturatedFats', NumberType::class, [
                'scale' => 2,
                'required' => false
            ])
            ->add('fibre', NumberType::class, [
                'scale' => 2,
                'required' => false
            ])
            ->add('carbohydrate', NumberType::class, [
                'scale' => 2,
                'required' => false
            ])
            ->add('sugar', NumberType::class, [
                'scale' => 2,
                'required' => false
            ])
            ->add('cholesterol', NumberType::class, [
                'scale' => 2,
                'required' => false
            ])
            ->add('sodium', NumberType::class, [
                'scale' => 2,
                'required' => false
            ])
            ->add('magnesium', NumberType::class, [
                'scale' => 2,
                'required' => false
            ])
            ->add('times_used', NumberType::class, [
                'data' => 0,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Food::class,
        ]);
    }
}
