<?php

namespace App\Controller\Back\Nutrition;

use App\Entity\Food;
use App\Form\FoodType;
use App\Repository\FoodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/nutrition/food')]
final class FoodController extends AbstractController
{
    #[Route(name: 'app_food_index', methods: ['GET'])]
    public function index(FoodRepository $foodRepository, Request $request): Response
    {
        $searchQuery = $request->query->get('search', '');
        if ($searchQuery) {
            $food = $foodRepository->findByNameLike($searchQuery);
        } else {
            $food = $foodRepository->findAll();
        }

        return $this->render('Back/Nutrition/food/index.html.twig', [
            'food' => $food,
            'searchQuery' => $searchQuery,
        ]);
    }

    #[Route('/view-only', name: 'app_food_view_only', methods: ['GET'])]
    public function viewOnly(FoodRepository $foodRepository): Response
    {
        $food = $foodRepository->findAll();
        return $this->render('Back/Nutrition/food/view_only.html.twig', [
            'food' => $food,
        ]);
    }

    #[Route('/new', name: 'app_food_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $food = new Food();
        $form = $this->createForm(FoodType::class, $food);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($food);
            $entityManager->flush();

            return $this->redirectToRoute('app_food_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Back/Nutrition/food/new.html.twig', [
            'food' => $food,
            'form' => $form,
        ]);
    }

    #[Route('/search', name: 'app_food_search', methods: ['GET'])]
    public function search(Request $request, FoodRepository $foodRepository): Response
    {
        try {
            $query = $request->query->get('q', '');
            
            if (empty($query)) {
                return $this->json([]);
            }
            
            $results = $foodRepository->findByNameLike($query);
            
            $data = array_map(function($food) {
                return [
                    'id' => $food->getId(),
                    'name' => $food->getName(),
                    'measure' => $food->getMeasure(),
                    'grams' => $food->getGrams(),
                    'calories' => $food->getCalories(),
                    'protein' => $food->getProtein(),
                    'fats' => $food->getFats(),
                    'saturatedFats' => $food->getSaturatedFats(),
                    'fibre' => $food->getFibre(),
                    'carbohydrate' => $food->getCarbohydrate(),
                    'sugar' => $food->getSugar(),
                    'cholesterol' => $food->getCholesterol(),
                    'sodium' => $food->getSodium(),
                    'magnesium' => $food->getMagnesium(),
                    'timesUsed' => $food->getTimesUsed()
                ];
            }, $results);
            
            return $this->json($data);
        } catch (\Exception $e) {
            // Log the error
            error_log('Food search error: ' . $e->getMessage());
            
            // Return a more specific error response
            return $this->json([
                'error' => 'An error occurred while searching for foods',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/name/{name}', name: 'app_food_get_by_name', methods: ['GET'])]
    public function getFoodByName(FoodRepository $foodRepository, string $name): Response
    {
        $food = $foodRepository->findOneBy(['name' => $name]);
        if (!$food) {
            return $this->json(['error' => 'Food not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($food);
    }

    #[Route('/id/{id}', name: 'app_food_get_by_id', methods: ['GET'])]
    public function getFoodById(FoodRepository $foodRepository, int $id): Response
    {
        $food = $foodRepository->find($id);
        if (!$food) {
            return $this->json(['error' => 'Food not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($food);
    }

    #[Route('/{id}', name: 'app_food_show', methods: ['GET'])]
    public function show(Food $food): Response
    {
        return $this->render('Back/Nutrition/food/show.html.twig', [
            'food' => $food,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_food_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Food $food, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FoodType::class, $food);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_food_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Back/Nutrition/food/edit.html.twig', [
            'food' => $food,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_food_delete', methods: ['POST'])]
    public function delete(Request $request, Food $food, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$food->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($food);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_food_index', [], Response::HTTP_SEE_OTHER);
    }
}
