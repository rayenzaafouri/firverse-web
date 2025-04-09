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
            ->add('discount_percentage')
            ->add('valid_from', null, [
                'widget' => 'single_text'
            ])
            ->add('valid_until', null, [
                'widget' => 'single_text'
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductDiscount::class,
        ]);
    }
}
