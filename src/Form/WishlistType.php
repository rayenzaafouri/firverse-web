<?php

namespace App\Form;

use App\Entity\Wishlist;
use App\Entity\product;
use App\Entity\user;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WishlistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('added_at', null, [
                'widget' => 'single_text'
            ])
            ->add('user_id', EntityType::class, [
                'class' => user::class,
'choice_label' => 'id',
            ])
            ->add('product_id', EntityType::class, [
                'class' => product::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Wishlist::class,
        ]);
    }
}
