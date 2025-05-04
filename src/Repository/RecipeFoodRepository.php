<?php

namespace App\Repository;

use App\Entity\RecipeFood;
use App\Entity\Recipe;
use App\Entity\Food;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RecipeFoodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeFood::class);
    }

    public function getServingSize(Recipe $recipe, Food $food): float
    {
        $recipeFood = $this->findOneBy([
            'recipe_id' => $recipe->getId(),
            'food_id' => $food->getId()
        ]);

        return $recipeFood ? $recipeFood->getServings() : 1.0;
    }

    public function updateServingSize(Recipe $recipe, Food $food, float $servings): void
    {
        $recipeFood = $this->findOneBy([
            'recipe_id' => $recipe->getId(),
            'food_id' => $food->getId()
        ]);

        if (!$recipeFood) {
            $recipeFood = new RecipeFood();
            $recipeFood->setRecipe($recipe);
            $recipeFood->setFood($food);
        }

        $recipeFood->setServings($servings);
        $this->getEntityManager()->persist($recipeFood);
        $this->getEntityManager()->flush();
    }
} 