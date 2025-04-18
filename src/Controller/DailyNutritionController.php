<?php

namespace App\Controller;

use App\Entity\Nutrition;
use App\Entity\Food;
use App\Entity\Recipe;
use App\Repository\NutritionRepository;
use App\Repository\FoodRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/nutrition/user/dailynutrition')]
class DailyNutritionController extends AbstractController
{
    private const DEFAULT_USER_ID = 18;
    private const MEAL_TYPES = ['breakfast', 'lunch', 'dinner', 'snack'];

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'app_daily_nutrition_index', methods: ['GET'])]
    public function index(NutritionRepository $nutritionRepository): Response
    {
        // Get today's date
        $today = new \DateTime();
        
        // Create nutrition records for the last 10 days if they don't exist
        for ($i = 0; $i < 10; $i++) {
            $date = clone $today;
            $date->modify("-{$i} days");
            
            $existingNutrition = $nutritionRepository->findOneBy([
                'user_id' => self::DEFAULT_USER_ID,
                'date' => $date
            ]);
            
            if (!$existingNutrition) {
                $nutrition = new Nutrition();
                $nutrition->setDate($date);
                $nutrition->setUserId(self::DEFAULT_USER_ID);
                
                $this->entityManager->persist($nutrition);
            }
        }
        
        // Flush all new nutrition records at once
        $this->entityManager->flush();

        // Get all nutrition records for the user
        $nutritions = $nutritionRepository->findBy(['user_id' => self::DEFAULT_USER_ID], ['date' => 'DESC']);

        return $this->render('daily_nutrition/index.html.twig', [
            'nutritions' => $nutritions,
        ]);
    }

    #[Route('/search-items', name: 'app_daily_nutrition_search_items', methods: ['GET'])]
    public function searchItems(Request $request, FoodRepository $foodRepository, RecipeRepository $recipeRepository): Response
    {
        $query = $request->query->get('q', '');
        
        // Search for foods
        $foods = $foodRepository->findByNameLike($query);
        $foodResults = array_map(function($food) {
            return [
                'id' => 'food_' . $food->getId(),
                'name' => $food->getName(),
                'type' => 'Food',
                'calories' => $food->getCalories(),
                'protein' => $food->getProtein(),
                'carbohydrate' => $food->getCarbohydrate(),
                'fats' => $food->getFats()
            ];
        }, $foods);

        // Search for recipes
        $recipes = $recipeRepository->findByNameLike($query);
        $recipeResults = array_map(function($recipe) {
            return [
                'id' => 'recipe_' . $recipe->getId(),
                'name' => $recipe->getName(),
                'type' => 'Recipe',
                'calories' => $recipe->getTotalCalories(),
                'protein' => $recipe->getTotalProtein(),
                'carbohydrate' => $recipe->getTotalCarbohydrate(),
                'fats' => $recipe->getTotalFats()
            ];
        }, $recipes);

        // Combine and sort results
        $results = array_merge($foodResults, $recipeResults);
        usort($results, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $this->json($results);
    }

    #[Route('/{date}', name: 'app_daily_nutrition_show', methods: ['GET'])]
    public function show(string $date, NutritionRepository $nutritionRepository, FoodRepository $foodRepository, RecipeRepository $recipeRepository, SessionInterface $session): Response
    {
        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        
        if (!$dateObj) {
            throw $this->createNotFoundException('Invalid date format');
        }

        $nutrition = $nutritionRepository->findOneBy([
            'user_id' => self::DEFAULT_USER_ID,
            'date' => $dateObj
        ]);

        if (!$nutrition) {
            // Create new nutrition record if it doesn't exist
            $nutrition = new Nutrition();
            $nutrition->setDate($dateObj);
            $nutrition->setUserId(self::DEFAULT_USER_ID);
            
            $this->entityManager->persist($nutrition);
            $this->entityManager->flush();
        }

        // Get all available foods and recipes
        $foods = $foodRepository->findAll();
        $recipes = $recipeRepository->findAll();

        // Get per-meal foods/recipes from session
        $mealFoods = $session->get('mealFoods', []);
        $mealRecipes = $session->get('mealRecipes', []);

        // For foods: If any food in nutrition is not in any meal, put it in breakfast
        $currentMealFoods = $mealFoods[$date] ?? [];
        $allMappedFoodIds = [];
        foreach ($currentMealFoods as $ids) {
            $allMappedFoodIds = array_merge($allMappedFoodIds, $ids);
        }
        $allMappedFoodIds = array_unique($allMappedFoodIds);
        foreach ($nutrition->getFoods() as $food) {
            if (!in_array($food->getId(), $allMappedFoodIds)) {
                $mealFoods[$date]['breakfast'][] = $food->getId();
                $mealFoods[$date]['breakfast'] = array_unique($mealFoods[$date]['breakfast']);
            }
        }
        // For recipes: If any recipe in nutrition is not in any meal, put it in breakfast
        $currentMealRecipes = $mealRecipes[$date] ?? [];
        $allMappedRecipeIds = [];
        foreach ($currentMealRecipes as $ids) {
            $allMappedRecipeIds = array_merge($allMappedRecipeIds, $ids);
        }
        $allMappedRecipeIds = array_unique($allMappedRecipeIds);
        foreach ($nutrition->getRecipes() as $recipe) {
            if (!in_array($recipe->getId(), $allMappedRecipeIds)) {
                $mealRecipes[$date]['breakfast'][] = $recipe->getId();
                $mealRecipes[$date]['breakfast'] = array_unique($mealRecipes[$date]['breakfast']);
            }
        }
        // Save back to session
        $session->set('mealFoods', $mealFoods);
        $session->set('mealRecipes', $mealRecipes);

        return $this->render('daily_nutrition/show.html.twig', [
            'nutrition' => $nutrition,
            'foods' => $foods,
            'recipes' => $recipes,
            'meal_types' => self::MEAL_TYPES,
            'mealFoods' => $mealFoods[$date] ?? [],
            'mealRecipes' => $mealRecipes[$date] ?? [],
        ]);
    }

    #[Route('/{date}/add-item/{mealType}', name: 'app_daily_nutrition_add_item', methods: ['POST'])]
    public function addItem(Request $request, string $date, string $mealType, FoodRepository $foodRepository, RecipeRepository $recipeRepository, NutritionRepository $nutritionRepository, SessionInterface $session): Response
    {
        if (!in_array($mealType, self::MEAL_TYPES)) {
            throw $this->createNotFoundException('Invalid meal type');
        }

        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        
        if (!$dateObj) {
            throw $this->createNotFoundException('Invalid date format');
        }

        $selectedItem = $request->request->get('selected_item');
        if (!$selectedItem) {
            throw $this->createNotFoundException('No item selected');
        }

        // Parse the selected item (format: "type_id")
        list($type, $id) = explode('_', $selectedItem);
        
        $nutrition = $nutritionRepository->findOneBy([
            'user_id' => self::DEFAULT_USER_ID,
            'date' => $dateObj
        ]);

        if (!$nutrition) {
            $nutrition = new Nutrition();
            $nutrition->setDate($dateObj);
            $nutrition->setUserId(self::DEFAULT_USER_ID);
        }

        if ($type === 'food') {
            $food = $foodRepository->find($id);
            if (!$food) {
                throw $this->createNotFoundException('Food not found');
            }
            $nutrition->addFood($food);
            // Track by meal in session
            $mealFoods = $session->get('mealFoods', []);
            $mealFoods[$date][$mealType][] = $food->getId();
            $mealFoods[$date][$mealType] = array_unique($mealFoods[$date][$mealType]);
            $session->set('mealFoods', $mealFoods);
        } elseif ($type === 'recipe') {
            $recipe = $recipeRepository->find($id);
            if (!$recipe) {
                throw $this->createNotFoundException('Recipe not found');
            }
            $nutrition->addRecipe($recipe);
            // Track by meal in session
            $mealRecipes = $session->get('mealRecipes', []);
            $mealRecipes[$date][$mealType][] = $recipe->getId();
            $mealRecipes[$date][$mealType] = array_unique($mealRecipes[$date][$mealType]);
            $session->set('mealRecipes', $mealRecipes);
        } else {
            throw $this->createNotFoundException('Invalid item type');
        }

        $this->entityManager->persist($nutrition);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_daily_nutrition_show', ['date' => $date]);
    }

    #[Route('/{date}/remove-food/{foodId}/{mealType}', name: 'app_daily_nutrition_remove_food', methods: ['POST'])]
    public function removeFood(string $date, int $foodId, string $mealType, FoodRepository $foodRepository, NutritionRepository $nutritionRepository, SessionInterface $session): Response
    {
        if (!in_array($mealType, self::MEAL_TYPES)) {
            throw $this->createNotFoundException('Invalid meal type');
        }

        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        
        if (!$dateObj) {
            throw $this->createNotFoundException('Invalid date format');
        }

        $food = $foodRepository->find($foodId);
        if (!$food) {
            throw $this->createNotFoundException('Food not found');
        }

        $nutrition = $nutritionRepository->findOneBy([
            'user_id' => self::DEFAULT_USER_ID,
            'date' => $dateObj
        ]);

        if ($nutrition) {
            $nutrition->removeFood($food);
            // Remove from session mealFoods
            $mealFoods = $session->get('mealFoods', []);
            if (isset($mealFoods[$date][$mealType])) {
                $mealFoods[$date][$mealType] = array_diff($mealFoods[$date][$mealType], [$foodId]);
                $session->set('mealFoods', $mealFoods);
            }
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_daily_nutrition_show', ['date' => $date]);
    }

    #[Route('/{date}/remove-recipe/{recipeId}/{mealType}', name: 'app_daily_nutrition_remove_recipe', methods: ['POST'])]
    public function removeRecipe(string $date, int $recipeId, string $mealType, RecipeRepository $recipeRepository, NutritionRepository $nutritionRepository, SessionInterface $session): Response
    {
        if (!in_array($mealType, self::MEAL_TYPES)) {
            throw $this->createNotFoundException('Invalid meal type');
        }

        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        
        if (!$dateObj) {
            throw $this->createNotFoundException('Invalid date format');
        }

        $recipe = $recipeRepository->find($recipeId);
        if (!$recipe) {
            throw $this->createNotFoundException('Recipe not found');
        }

        $nutrition = $nutritionRepository->findOneBy([
            'user_id' => self::DEFAULT_USER_ID,
            'date' => $dateObj
        ]);

        if ($nutrition) {
            $nutrition->removeRecipe($recipe);
            // Remove from session mealRecipes
            $mealRecipes = $session->get('mealRecipes', []);
            if (isset($mealRecipes[$date][$mealType])) {
                $mealRecipes[$date][$mealType] = array_diff($mealRecipes[$date][$mealType], [$recipeId]);
                $session->set('mealRecipes', $mealRecipes);
            }
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_daily_nutrition_show', ['date' => $date]);
    }
}