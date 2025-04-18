<?php

namespace App\Form;

use App\Entity\Coupon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class CouponType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Coupon Code',
                'empty_data' => '',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('discount_percentage', NumberType::class, [
                'label' => 'Discount Percentage',
                'empty_data' => 0,
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('min_order_amount', NumberType::class, [
                'label' => 'Minimum Order Amount',
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
                'attr' => ['class' => 'form-control datetimepicker'],
                'empty_data' => null,
            ])
            ->add('is_active', CheckboxType::class, [
                'label' => 'Is Active',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coupon::class,
            'empty_data' => null,
        ]);
    }
}