<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NutritionPreferencesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Diet selection
            ->add('diet', ChoiceType::class, [
                'label'    => 'Diet Type',
                'choices'  => [
                    'Standard'       => 'standard',
                    'Keto'           => 'keto',
                    'Carnivore'      => 'carnivore',
                    'Vegetarian'     => 'vegetarian',
                    'Mediterranean'  => 'mediterranean',
                ],
                'expanded' => false,
            ])
            // Disliked foods input
            ->add('dislikes', TextareaType::class, [
                'label'    => 'Foods You Hate',
                'required' => false,
                'attr'     => ['placeholder' => 'e.g. broccoli, mushrooms, tofu'],
            ])
            // Macronutrient percentages
            ->add('proteinPct', IntegerType::class, [
                'label' => 'Protein (%)',
                'attr'  => ['min' => 0, 'max' => 100],
                'data'  => 30,
            ])
            ->add('fatPct', IntegerType::class, [
                'label' => 'Fat (%)',
                'attr'  => ['min' => 0, 'max' => 100],
                'data'  => 20,
            ])
            ->add('carbPct', IntegerType::class, [
                'label' => 'Carbs (%)',
                'attr'  => ['min' => 0, 'max' => 100],
                'data'  => 50,
            ])
            // Submit
            ->add('generate', SubmitType::class, [
                'label' => 'Get Nutrition Plan',
                'attr'  => ['class' => 'btn btn-primary mt-3'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
