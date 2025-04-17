<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Food;
use App\Entity\User;
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

#[Route('/admin/recipe')]
class AdminRecipeController extends AbstractController
{
    // Hardcoded user ID for all recipes
    private const DEFAULT_USER_ID = 35;

    #[Route('/', name: 'app_admin_recipe_index', methods: ['GET'])]
    public function index(RecipeRepository $recipeRepository): Response
    {
        // Admin can see all recipes
        $recipes = $recipeRepository->findAll();
        
        return $this->render('admin_recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/new', name: 'app_admin_recipe_new', methods: ['GET', 'POST'])]
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
            
            $entityManager->persist($recipe);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_recipe/new.html.twig', [
            'recipe' => $recipe,
            'form' => $form,
            'foods' => $foodRepository->findAll(),
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_recipe_show', methods: ['GET'])]
    public function show(Recipe $recipe): Response
    {
        // Admin can view any recipe
        return $this->render('admin_recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_recipe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Recipe $recipe, EntityManagerInterface $entityManager, FoodRepository $foodRepository, UserRepository $userRepository): Response
    {
        // Admin can edit any recipe
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Always use the hardcoded user ID
            $user = $userRepository->find(self::DEFAULT_USER_ID);
            if ($user) {
                $recipe->setUser($user);
            }
            
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_recipe/edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form,
            'foods' => $foodRepository->findAll(),
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_recipe_delete', methods: ['POST'])]
    public function delete(Request $request, Recipe $recipe, EntityManagerInterface $entityManager): Response
    {
        // Admin can delete any recipe
        if ($this->isCsrfTokenValid('delete'.$recipe->getId(), $request->request->get('_token'))) {
            $entityManager->remove($recipe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_recipe_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/search-foods', name: 'app_admin_recipe_search_foods', methods: ['GET'])]
    public function searchFoods(Request $request, FoodRepository $foodRepository): Response
    {
        $query = $request->query->get('q', '');
        $foods = $foodRepository->findByNameLike($query);
        
        $results = array_map(function($food) {
            return [
                'id' => $food->getId(),
                'name' => $food->getName(),
            ];
        }, $foods);
        
        return $this->json($results);
    }
} 