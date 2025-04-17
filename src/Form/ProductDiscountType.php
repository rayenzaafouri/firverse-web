<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductDiscount;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('product', EntityType::class, [
            'class'        => Product::class,
            'choice_label' => 'name',
            'placeholder'  => '— select a product —',
            'attr'         => ['class' => 'form-control'],
        ])
        ->add('discount_percentage', null, [
            'attr'        => ['class' => 'form-control', 'min' => 1, 'max' => 100],
        ])
        ->add('valid_from', null, [
            'widget'      => 'single_text',
            'attr'        => ['class' => 'form-control'],
        ])
        ->add('valid_until', null, [
            'widget'      => 'single_text',
            'attr'        => ['class' => 'form-control'],
        ])
    ;
}
}
