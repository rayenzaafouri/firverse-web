<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Product Name',
                'empty_data' => '',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('price', NumberType::class, [
                'label' => 'Price',
                'empty_data' => 0.0,
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('brand', TextType::class, [
                'label' => 'Brand',
                'empty_data' => '',

                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'empty_data' => '',

                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4]
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'empty_data' => 0,

                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('imageUrl', UrlType::class, [
                'label' => 'Image URL',
                'empty_data' => '',

                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'empty_data' => '',

                'placeholder' => 'Choose a Category',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
