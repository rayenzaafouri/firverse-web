<?php

namespace App\Form;

use App\Entity\Exercice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Field type imports:
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
// For CallbackTransformer (if using JSON transformation):
use Symfony\Component\Form\CallbackTransformer;

class ExerciceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Exercise Name',
                'attr' => [
                    'class' => 'form-control' 
                ],
                'empty_data' => ''
            ])

            ->add('steps', TextareaType::class, [
                'label' => 'Correct steps',
                'attr' => [
                    'class' => 'json-assist json-assist-steps',
                ],
                'empty_data' => ''
            ])
            ->add('sets', IntegerType::class, [
                'label' => 'Number of sets',
                'attr' => [
                    'placeholder' => '10', // User sees this
                    'class' => 'form-control',// CSS class

                ],
                'empty_data' => 0
            ])
            ->add('reps', IntegerType::class, [
                'label' => 'Number of repetitions',
                'attr' => [
                    'placeholder' => '3', // User sees this
                    'class' => 'form-control' // CSS class
                ],
                'empty_data' => 0
            ])
            ->add('grips', ChoiceType::class, [
                'label' => 'Target Muscle',
                'attr' => [
                    'class' => 'form-control' // CSS class
                ],
                'choices' => [
                    'Abdominals' => 'abdominals',
                    'Biceps' => 'biceps',
                    'Calves' => 'calves',
                    'Chest' => 'chest',
                    'Forearms' => 'forearms',
                    'Front Shoulders' => 'front-shoulders',
                    'Glutes' => 'glutes',
                    'Hamstrings' => 'hamstrings',
                    'Hands' => 'hands',
                    'Lats' => 'lats',
                    'Lower Back' => 'lowerback',
                    'Obliques' => 'obliques',
                    'Quads' => 'quads',
                    'Rear Shoulders' => 'rear-shoulders',
                    'Traps' => 'traps',
                    'Middle Traps' => 'traps-middle',
                    'Triceps' => 'triceps'
                ],
                'placeholder' => 'Pick a grip',
                'empty_data' => 'overhand'
            ])
            ->add('difficulty', ChoiceType::class, [
                'label' => 'Difficulty',
                'attr' => [
                    'class' => 'form-control dropdown-toggle' 
                ],
                'choices' => [
                    'Beginner' => 'beginner',
                    'Novice' => 'novice',
                    'Intermediate' => 'intermediate',
                    'Advanced' => 'advanced',
                ],
                'placeholder' => 'Pick a level',
                'empty_data' => 'novice'
            ])
            ->add('body_map_front', TextType::class, [
                'label' => 'Front body map image',
                'attr' => [
                    'placeholder' => 'http://example.com/image.png', // User sees this
                    'class' => 'form-control' // CSS class
                ],
                'empty_data' => ''
                // Server receives: string value
                // Database stores: string in title column
            ])
            ->add('body_map_back', TextType::class, [
                'label' => 'Back body map image',
                'attr' => [
                    'placeholder' => 'http://example.com/image.png', // User sees this
                    'class' => 'form-control' // CSS class
                ],
                'empty_data' => ''
                // Server receives: string value
                // Database stores: string in title column
            ])
            ->add('video_url', TextType::class, [
                'label' => 'Embedded video URL',
                'attr' => [
                    'placeholder' => 'http://youtube.com/embed/ViDeoID', // User sees this
                    'class' => 'form-control' // CSS class
                ],
                'empty_data' => ''

                // Server receives: string value
                // Database stores: string in title column
            ])

            ->add('m_primary', CheckboxType::class, [
                'label' => 'Primary muscles',
                'attr' => [
                    'class' => 'form-check-input' // CSS class
                ],
            ])

            ->add('m_secondary', CheckboxType::class, [
                'label' => 'Secondary muscles',
                'attr' => [
                    'class' => 'form-check-input' // CSS class
                ],

            ])

            ->add('m_tertiary', CheckboxType::class, [
                'label' => 'Tertiary muscles',
                'attr' => [
                    'class' => 'form-check-input' // CSS class
                ],

            ])

            ->add('equipment', TextareaType::class, [
                'label' => 'Equipment',
                'attr' => [
                    'class' => 'json-assist json-assist-equipment',
                ],
                'empty_data' => '[]'
                
            ])
            ->add('push', CheckboxType::class, [
                'label' => 'Push',
                'attr' => [
                    'class' => 'movement-radio',
                ],
            ])
            ->add('pull', CheckboxType::class, [
                'label' => 'Pull',
                'attr' => [
                    'class' => 'movement-radio',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Exercice::class,
            'csrf_protection' => false
        ]);
    }
}
