<?php

namespace App\Controller\Front\Nutrition;

use App\Entity\Nutrition;
use App\Entity\Food;
use App\Entity\Recipe;
use App\Entity\Waterconsumption;
use App\Repository\NutritionRepository;
use App\Repository\FoodRepository;
use App\Repository\RecipeRepository;
use App\Repository\WaterconsumptionRepository;
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
    public function index(NutritionRepository $nutritionRepository, WaterconsumptionRepository $waterconsumptionRepository): Response
    {
        // Get today's date
        $today = new \DateTime();
        
        // Create nutrition and waterconsumption records for the last 10 days if they don't exist
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

            // Waterconsumption check
            $existingWater = $waterconsumptionRepository->findOneBy([
                'user' => self::DEFAULT_USER_ID,
                'ConsumptionDate' => $date
            ]);
            if (!$existingWater) {
                $water = new Waterconsumption();
                $water->setUser($this->entityManager->getReference('App\\Entity\\User', self::DEFAULT_USER_ID));
                $water->setConsumptionDate($date);
                $water->setAmountConsumed(0);
                $this->entityManager->persist($water);
            }
        }
        // Flush all new records at once
        $this->entityManager->flush();

        // Get all nutrition records for the user
        $nutritions = $nutritionRepository->findBy(['user_id' => self::DEFAULT_USER_ID], ['date' => 'DESC']);

        return $this->render('Front/Nutrition/daily_nutrition/index.html.twig', [
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
    public function show(string $date, FoodRepository $foodRepository, RecipeRepository $recipeRepository, NutritionRepository $nutritionRepository, SessionInterface $session): Response
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
            $nutrition = new Nutrition();
            $nutrition->setUserId(self::DEFAULT_USER_ID);
            $nutrition->setDate($dateObj);
            $nutrition->setEntityManager($this->entityManager);
            $this->entityManager->persist($nutrition);
            $this->entityManager->flush();
        } else {
            $nutrition->setEntityManager($this->entityManager);
        }

        $foods = $foodRepository->findAll();
        $recipes = $recipeRepository->findAll();

        // Get foods and recipes for each meal type
        $mealFoods = [];
        $mealRecipes = [];
        foreach (Nutrition::MEAL_TYPES as $mealType) {
            $mealFoods[$mealType] = $nutrition->getFoodsByMealType($mealType);
            $mealRecipes[$mealType] = $nutrition->getRecipesByMealType($mealType);
        }

        return $this->render('Front/Nutrition/daily_nutrition/show.html.twig', [
            'nutrition' => $nutrition,
            'foods' => $foods,
            'recipes' => $recipes,
            'meal_types' => Nutrition::MEAL_TYPES,
            'mealFoods' => $mealFoods,
            'mealRecipes' => $mealRecipes,
        ]);
    }

    #[Route('/{date}/add-item/{mealType}', name: 'app_daily_nutrition_add_item', methods: ['POST'])]
    public function addItem(Request $request, string $date, string $mealType, FoodRepository $foodRepository, RecipeRepository $recipeRepository, NutritionRepository $nutritionRepository, SessionInterface $session): Response
    {
        if (!in_array($mealType, Nutrition::MEAL_TYPES)) {
            throw $this->createNotFoundException('Invalid meal type');
        }

        $type = $request->request->get('type');
        $id = $request->request->get('id');
        $serving = (int)$request->request->get('serving', 1);

        if (!$type || !$id) {
            throw $this->createNotFoundException('Missing required parameters');
        }

        $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateObj) {
            throw $this->createNotFoundException('Invalid date format');
        }

        $nutrition = $nutritionRepository->findOneBy([
            'user_id' => self::DEFAULT_USER_ID,
            'date' => $dateObj
        ]);

        if (!$nutrition) {
            $nutrition = new Nutrition();
            $nutrition->setUserId(self::DEFAULT_USER_ID);
            $nutrition->setDate($dateObj);
            $nutrition->setEntityManager($this->entityManager);
        } else {
            $nutrition->setEntityManager($this->entityManager);
        }

        if ($type === 'food') {
            $food = $foodRepository->find($id);
            if (!$food) {
                throw $this->createNotFoundException('Food not found');
            }
            $nutrition->addFood($food, $mealType, $serving);
        } elseif ($type === 'recipe') {
            $recipe = $recipeRepository->find($id);
            if (!$recipe) {
                throw $this->createNotFoundException('Recipe not found');
            }
            $nutrition->addRecipe($recipe, $mealType, $serving);
        } else {
            throw $this->createNotFoundException('Invalid item type');
        }

        $this->entityManager->persist($nutrition);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_daily_nutrition_show', ['date' => $date]);
    }

    #[Route('/{date}/remove-food/{foodId}/{mealType}', name: 'app_daily_nutrition_remove_food', methods: ['POST'])]
    public function removeFood(string $date, int $foodId, string $mealType, FoodRepository $foodRepository, NutritionRepository $nutritionRepository): Response
    {
        if (!in_array($mealType, Nutrition::MEAL_TYPES)) {
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
            $nutrition->setEntityManager($this->entityManager);
            $nutrition->removeFood($food, $mealType);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_daily_nutrition_show', ['date' => $date]);
    }

    #[Route('/{date}/remove-recipe/{recipeId}/{mealType}', name: 'app_daily_nutrition_remove_recipe', methods: ['POST'])]
    public function removeRecipe(string $date, int $recipeId, string $mealType, RecipeRepository $recipeRepository, NutritionRepository $nutritionRepository): Response
    {
        if (!in_array($mealType, Nutrition::MEAL_TYPES)) {
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
            $nutrition->setEntityManager($this->entityManager);
            $nutrition->removeRecipe($recipe, $mealType);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_daily_nutrition_show', ['date' => $date]);
    }

    #[Route('/{date}/update-food-serving/{foodId}/{mealType}', name: 'app_daily_nutrition_update_food_serving', methods: ['POST'])]
    public function updateFoodServing(Request $request, string $date, int $foodId, string $mealType, FoodRepository $foodRepository, NutritionRepository $nutritionRepository): Response
    {
        if (!in_array($mealType, Nutrition::MEAL_TYPES)) {
            throw $this->createNotFoundException('Invalid meal type');
        }

        $serving = (int)$request->request->get('serving', 1);
        if ($serving < 1) {
            throw $this->createNotFoundException('Invalid serving size');
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
            $nutrition->setEntityManager($this->entityManager);
            $nutrition->updateFoodServing($food, $mealType, $serving);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_daily_nutrition_show', ['date' => $date]);
    }

    #[Route('/{date}/update-recipe-serving/{recipeId}/{mealType}', name: 'app_daily_nutrition_update_recipe_serving', methods: ['POST'])]
    public function updateRecipeServing(Request $request, string $date, int $recipeId, string $mealType, RecipeRepository $recipeRepository, NutritionRepository $nutritionRepository): Response
    {
        if (!in_array($mealType, Nutrition::MEAL_TYPES)) {
            throw $this->createNotFoundException('Invalid meal type');
        }

        $serving = (int)$request->request->get('serving', 1);
        if ($serving < 1) {
            throw $this->createNotFoundException('Invalid serving size');
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
            $nutrition->setEntityManager($this->entityManager);
            $nutrition->updateRecipeServing($recipe, $mealType, $serving);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_daily_nutrition_show', ['date' => $date]);
    }
}