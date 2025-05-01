<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductDiscount;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ProductDiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'label' => 'Product Name',
                'placeholder' => '— select a product —',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('discount_percentage', NumberType::class, [
                'label' => 'Discount Percentage',
                'empty_data' => 0,
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('valid_from', DateTimeType::class, [
                'label' => 'Valid From',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control datetimepicker'],
                'empty_data' => null,
            ])
            ->add('valid_until', DateTimeType::class, [
                'label' => 'Valid Until',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control '],
                'empty_data' => null,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductDiscount::class,
        ]);
    }
}
