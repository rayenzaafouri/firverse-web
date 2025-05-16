<?php

namespace App\Controller\Front\Nutrition;

use App\Repository\UserRepository;
use App\Repository\NutritionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProgressController extends AbstractController
{
    #[Route('/nutrition/user/progress', name: 'app_nutrition_progress')]
    public function index(UserRepository $userRepository, NutritionRepository $nutritionRepository, EntityManagerInterface $em): Response
    {
        // Hardcode user with id 33
        $user = $userRepository->find(100);
        if ($user && method_exists($user, 'getGender')) {
            $gender = $user->getGender();
        } else {
            $gender = 'male'; // fallback
        }
        if ($user && method_exists($user, 'getBirth_date')) {
            $birthDate = $user->getBirth_date();
        } else {
            $birthDate = new \DateTime('-30 years'); // fallback
        }
        $age = $birthDate instanceof \DateTimeInterface ? $birthDate->diff(new \DateTime())->y : 30;

        // Random weight and activity level
        if (strtolower($gender) === 'male') {
            $weight = rand(75, 85); // kg
            $height = 178; // average male height in cm
        } else {
            $weight = rand(55, 65); // kg
            $height = 165; // average female height in cm
        }

        $activityLevels = [
            ['label' => 'Sedentary', 'multiplier' => 1.2],
            ['label' => 'Lightly active', 'multiplier' => 1.375],
            ['label' => 'Moderately active', 'multiplier' => 1.55],
            ['label' => 'Very active', 'multiplier' => 1.725],
            ['label' => 'Super active', 'multiplier' => 1.9],
        ];
        $activity = $activityLevels[array_rand($activityLevels)];
        $activityMultiplier = $activity['multiplier'];

        // Harris-Benedict BMR formula
        if (strtolower($gender) === 'male') {
            $bmr = 88.362 + (13.397 * $weight) + (4.799 * $height) - (5.677 * $age);
        } else {
            $bmr = 447.593 + (9.247 * $weight) + (3.098 * $height) - (4.330 * $age);
        }
        $calories = (int) round($bmr * $activityMultiplier);
        // Clamp calories between 2800 and 2899
        $calories = max(2800, min(2899, $calories));

        // Fetch today's nutrition for user 33
        $today = new \DateTimeImmutable('today');
        $nutrition = $nutritionRepository->findOneBy(['user_id' => 33, 'date' => $today]);
        $perMeal = [];
        $totals = [
            'calories' => 0,
            'carbs' => 0,
            'fats' => 0,
            'proteins' => 0,
            'saturated_fats' => 0,
            'cholesterol' => 0,
            'fiber' => 0,
            'sugar' => 0,
            'potassium' => 0,
            'sodium' => 0,
        ];
        if ($nutrition) {
            $nutrition->setEntityManager($em);
            foreach (\App\Entity\Nutrition::MEAL_TYPES as $mealType) {
                $mealTotals = [
                    'calories' => 0,
                    'carbs' => 0,
                    'fats' => 0,
                    'proteins' => 0,
                ];
                // Foods
                $foods = $nutrition->getFoodsByMealType($mealType);
                foreach ($foods as $food) {
                    $nf = $nutrition->getNutritionFood($food, $mealType);
                    $serving = $nf ? $nf->getServing() : 1;
                    $mealTotals['calories'] += ($food->getCalories() ?? 0) * $serving;
                    $mealTotals['carbs'] += ($food->getCarbohydrate() ?? 0) * $serving;
                    $mealTotals['fats'] += ($food->getFats() ?? 0) * $serving;
                    $mealTotals['proteins'] += ($food->getProtein() ?? 0) * $serving;
                    $totals['saturated_fats'] += ($food->getSaturatedFats() ?? 0) * $serving;
                    $totals['cholesterol'] += ($food->getCholesterol() ?? 0) * $serving;
                    $totals['fiber'] += ($food->getFibre() ?? 0) * $serving;
                    $totals['sugar'] += ($food->getSugar() ?? 0) * $serving;
                    $totals['potassium'] += 0; // Add if available in Food entity
                    $totals['sodium'] += ($food->getSodium() ?? 0) * $serving;
                }
                // Recipes
                $recipes = $nutrition->getRecipesByMealType($mealType);
                foreach ($recipes as $recipe) {
                    $nr = $nutrition->getNutritionRecipe($recipe, $mealType);
                    $serving = $nr ? $nr->getServing() : 1;
                    foreach ($recipe->getFoods() as $food) {
                        $rfServing = method_exists($recipe, 'getFoodServingSize') ? $recipe->getFoodServingSize($food) : 1;
                        $totalServing = $serving * $rfServing;
                        $mealTotals['calories'] += ($food->getCalories() ?? 0) * $totalServing;
                        $mealTotals['carbs'] += ($food->getCarbohydrate() ?? 0) * $totalServing;
                        $mealTotals['fats'] += ($food->getFats() ?? 0) * $totalServing;
                        $mealTotals['proteins'] += ($food->getProtein() ?? 0) * $totalServing;
                        $totals['saturated_fats'] += ($food->getSaturatedFats() ?? 0) * $totalServing;
                        $totals['cholesterol'] += ($food->getCholesterol() ?? 0) * $totalServing;
                        $totals['fiber'] += ($food->getFibre() ?? 0) * $totalServing;
                        $totals['sugar'] += ($food->getSugar() ?? 0) * $totalServing;
                        $totals['potassium'] += 0; // Add if available in Food entity
                        $totals['sodium'] += ($food->getSodium() ?? 0) * $totalServing;
                    }
                }
                $perMeal[$mealType] = $mealTotals;
                $totals['calories'] += $mealTotals['calories'];
                $totals['carbs'] += $mealTotals['carbs'];
                $totals['fats'] += $mealTotals['fats'];
                $totals['proteins'] += $mealTotals['proteins'];
            }
        }

        // Macro targets based on calorie goal
        $carb_target_g = round(($calories * 0.4) / 4); // 40% of calories, 4 kcal/g
        $fat_target_g = round(($calories * 0.3) / 9);  // 30% of calories, 9 kcal/g
        $protein_target_g = round(($calories * 0.3) / 4); // 30% of calories, 4 kcal/g

        // Common micronutrient DVs (for 2000 kcal)
        $dv = [
            'saturated_fats' => 20, // g
            'polyunsaturated_fats' => 11, // g (approximate)
            'monounsaturated_fats' => 17, // g (approximate)
            'cholesterol' => 300, // mg
            'fiber' => 28, // g
            'sugar' => 50, // g
            'potassium' => 4700, // mg
            'sodium' => 2300, // mg
        ];
        $scaling = $calories / 2000;
        $targets = [
            'saturated_fats' => round($dv['saturated_fats'] * $scaling, 1),
            'polyunsaturated_fats' => round($dv['polyunsaturated_fats'] * $scaling, 1),
            'monounsaturated_fats' => round($dv['monounsaturated_fats'] * $scaling, 1),
            'cholesterol' => round($dv['cholesterol'] * $scaling),
            'fiber' => round($dv['fiber'] * $scaling, 1),
            'sugar' => round($dv['sugar'] * $scaling, 1),
            'potassium' => round($dv['potassium'] * $scaling),
            'sodium' => round($dv['sodium'] * $scaling),
        ];

        // Generate polyunsaturated and monounsaturated fats
        $polyunsaturated_fats = round($totals['saturated_fats'] * 0.4, 1);
        $monounsaturated_fats = round($totals['saturated_fats'] * 0.55, 1);

        return $this->render('Front/Nutrition/progress.html.twig', [
            'goals' => [
                'calories' => $totals['calories'] ?: $calories,
                'carbs' => ['g' => $totals['carbs'], 'target' => $carb_target_g, 'percent' => 40],
                'fats' => ['g' => $totals['fats'], 'target' => $fat_target_g, 'percent' => 30],
                'proteins' => ['g' => $totals['proteins'], 'target' => $protein_target_g, 'percent' => 30],
                'custom' => (bool)$nutrition,
            ],
            'meals' => [
                'Breakfast', 'Lunch', 'Dinner', 'Snacks'
            ],
            'calories_per_meal' => $perMeal,
            'micronutrients' => [
                ['label' => 'Saturated fat', 'value' => $totals['saturated_fats'] . ' g', 'target' => $targets['saturated_fats'] . ' g'],
                ['label' => 'Polyunsaturated fat', 'value' => $polyunsaturated_fats . ' g', 'target' => $targets['polyunsaturated_fats'] . ' g'],
                ['label' => 'Monounsaturated fat', 'value' => $monounsaturated_fats . ' g', 'target' => $targets['monounsaturated_fats'] . ' g'],
                ['label' => 'Cholesterol', 'value' => $totals['cholesterol'] . ' mg', 'target' => $targets['cholesterol'] . ' mg'],
                ['label' => 'Fiber', 'value' => $totals['fiber'] . ' g', 'target' => $targets['fiber'] . ' g'],
                ['label' => 'Sugar', 'value' => $totals['sugar'] . ' g', 'target' => $targets['sugar'] . ' g'],
                ['label' => 'Potassium', 'value' => $totals['potassium'] . ' mg', 'target' => $targets['potassium'] . ' mg'],
                ['label' => 'Sodium', 'value' => $totals['sodium'] . ' mg', 'target' => $targets['sodium'] . ' mg'],
            ],
            'user_info' => [
                'gender' => $gender,
                'age' => $age,
                'weight' => $weight,
                'height' => $height,
                'activity' => $activity['label'],
                'calorie_goal' => $calories,
            ],
        ]);
    }
} 