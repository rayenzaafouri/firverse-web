<?php

namespace App\Controller\Front\Nutrition;

use App\Entity\Recipe;
use App\Entity\Food;
use App\Entity\User;
use App\Entity\RecipeFood;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use App\Repository\FoodRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/nutrition/recipe')]
class RecipeController extends AbstractController
{
    // Hardcoded user ID for all recipes
    private const DEFAULT_USER_ID = 33;

    #[Route('/', name: 'app_recipe_index', methods: ['GET'])]
    public function index(RecipeRepository $recipeRepository): Response
    {
        // Filter recipes by the hardcoded user ID
        $recipes = $recipeRepository->findBy(['user' => self::DEFAULT_USER_ID]);
        
        return $this->render('Front/Nutrition/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/search-foods', name: 'app_recipe_search_foods', methods: ['GET'])]
    public function searchFoods(Request $request, FoodRepository $foodRepository): Response
    {
        $query = $request->query->get('q', '');
        $foods = $foodRepository->findByNameLike($query);
        
        $results = array_map(function($food) {
            return [
                'id' => $food->getId(),
                'name' => $food->getName(),
                'calories' => $food->getCalories(),
                'protein' => $food->getProtein(),
                'carbohydrate' => $food->getCarbohydrate(),
                'fats' => $food->getFats(),
                'fiber' => $food->getFibre(),
                'sugar' => $food->getSugar(),
                'sodium' => $food->getSodium(),
                'potassium' => $food->getMagnesium()
            ];
        }, $foods);
        
        return $this->json($results);
    }

    #[Route('/new', name: 'app_recipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, FoodRepository $foodRepository, UserRepository $userRepository): Response
    {
        $recipe = new Recipe();
        // Set times_used to 0 by default
        $recipe->setTimes_used(0);
        
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Always use the hardcoded user ID
            $user = $userRepository->find(self::DEFAULT_USER_ID);
            if ($user) {
                $recipe->setUser($user);
            }
            
            // Process the foods data from the form
            $foodIds = $request->request->all('recipe')['foods'] ?? [];
            $servingSizes = $request->request->all('serving_size') ?? [];
            
            // Add foods with their serving sizes
            foreach ($foodIds as $foodId) {
                $food = $foodRepository->find($foodId);
                if ($food) {
                    $servingSize = isset($servingSizes[$foodId]) ? (int)round($servingSizes[$foodId]) : 1;
                    $recipe->addFood($food, $servingSize);
                }
            }
            
            $entityManager->persist($recipe);
            $entityManager->flush();

            // After recipe is persisted, create RecipeFood entries
            foreach ($foodIds as $foodId) {
                if (isset($servingSizes[$foodId])) {
                    $servingSize = (int)round($servingSizes[$foodId]);
                    
                    // Delete existing entry if any
                    $entityManager->getConnection()->executeStatement(
                        'DELETE FROM recipe_food WHERE recipe_id = :recipe_id AND food_id = :food_id',
                        [
                            'recipe_id' => $recipe->getId(),
                            'food_id' => $foodId
                        ]
                    );
                    
                    // Insert new entry
                    $entityManager->getConnection()->executeStatement(
                        'INSERT INTO recipe_food (recipe_id, food_id, servings) VALUES (:recipe_id, :food_id, :servings)',
                        [
                            'recipe_id' => $recipe->getId(),
                            'food_id' => $foodId,
                            'servings' => $servingSize
                        ]
                    );
                }
            }

            return $this->redirectToRoute('app_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Front/Nutrition/recipe/new.html.twig', [
            'recipe' => $recipe,
            'form' => $form,
            'foods' => $foodRepository->findAll(),
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_recipe_show', methods: ['GET'])]
    public function show(Recipe $recipe): Response
    {
        // Check if the recipe belongs to the hardcoded user
        if ($recipe->getUser() && $recipe->getUser()->getId() !== self::DEFAULT_USER_ID) {
            throw $this->createNotFoundException('Recipe not found or access denied.');
        }
        
        return $this->render('Front/Nutrition/recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_recipe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recipe $recipe, EntityManagerInterface $entityManager, FoodRepository $foodRepository, UserRepository $userRepository): Response
    {
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        // Load existing serving sizes
        $existingServingSizes = [];
        $stmt = $entityManager->getConnection()->executeQuery(
            'SELECT food_id, servings FROM recipe_food WHERE recipe_id = :recipe_id',
            ['recipe_id' => $recipe->getId()]
        );
        while ($row = $stmt->fetchAssociative()) {
            $existingServingSizes[$row['food_id']] = $row['servings'];
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // Always use the hardcoded user ID
            $user = $userRepository->find(self::DEFAULT_USER_ID);
            if ($user) {
                $recipe->setUser($user);
            }
            
            // Process the foods data from the form
            $foodIds = $request->request->all('recipe')['foods'] ?? [];
            $servingSizes = $request->request->all('serving_size') ?? [];
            
            // Get current food IDs from recipe_food table
            $currentFoodIds = [];
            $stmt = $entityManager->getConnection()->executeQuery(
                'SELECT food_id FROM recipe_food WHERE recipe_id = :recipe_id',
                ['recipe_id' => $recipe->getId()]
            );
            while ($row = $stmt->fetchAssociative()) {
                $currentFoodIds[] = $row['food_id'];
            }
            
            // Delete foods that are no longer in the recipe
            foreach ($currentFoodIds as $foodId) {
                if (!in_array($foodId, $foodIds)) {
                    $entityManager->getConnection()->executeStatement(
                        'DELETE FROM recipe_food WHERE recipe_id = :recipe_id AND food_id = :food_id',
                        [
                            'recipe_id' => $recipe->getId(),
                            'food_id' => $foodId
                        ]
                    );
                }
            }
            
            // Clear existing foods
            foreach ($recipe->getFoods() as $food) {
                $recipe->removeFood($food);
            }
            
            // Add new foods with their serving sizes
            foreach ($foodIds as $foodId) {
                $food = $foodRepository->find($foodId);
                if ($food) {
                    $servingSize = isset($servingSizes[$foodId]) ? (int)round($servingSizes[$foodId]) : 1;
                    $recipe->addFood($food, $servingSize);
                }
            }
            
            $entityManager->flush();

            // After recipe is updated, update RecipeFood entries
            foreach ($foodIds as $foodId) {
                if (isset($servingSizes[$foodId])) {
                    $servingSize = (int)round($servingSizes[$foodId]);
                    
                    // Delete existing entry if any
                    $entityManager->getConnection()->executeStatement(
                        'DELETE FROM recipe_food WHERE recipe_id = :recipe_id AND food_id = :food_id',
                        [
                            'recipe_id' => $recipe->getId(),
                            'food_id' => $foodId
                        ]
                    );
                    
                    // Insert new entry
                    $entityManager->getConnection()->executeStatement(
                        'INSERT INTO recipe_food (recipe_id, food_id, servings) VALUES (:recipe_id, :food_id, :servings)',
                        [
                            'recipe_id' => $recipe->getId(),
                            'food_id' => $foodId,
                            'servings' => $servingSize
                        ]
                    );
                }
            }

            return $this->redirectToRoute('app_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Front/Nutrition/recipe/edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form,
            'foods' => $foodRepository->findAll(),
            'users' => $userRepository->findAll(),
            'existingServingSizes' => $existingServingSizes,
        ]);
    }

    #[Route('/{id}', name: 'app_recipe_delete', methods: ['POST'])]
    public function delete(Request $request, Recipe $recipe, EntityManagerInterface $entityManager): Response
    {
        // Check if the recipe belongs to the hardcoded user
        if ($recipe->getUser() && $recipe->getUser()->getId() !== self::DEFAULT_USER_ID) {
            throw $this->createNotFoundException('Recipe not found or access denied.');
        }
        
        if ($this->isCsrfTokenValid('delete'.$recipe->getId(), $request->request->get('_token'))) {
            $entityManager->remove($recipe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_recipe_index', [], Response::HTTP_SEE_OTHER);
    }
} 