<?php

namespace App\Twig;

use App\Repository\RecipeFoodRepository;
use App\Entity\Recipe;
use App\Entity\Food;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RecipeExtension extends AbstractExtension
{
    private $recipeFoodRepository;

    public function __construct(RecipeFoodRepository $recipeFoodRepository)
    {
        $this->recipeFoodRepository = $recipeFoodRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_recipe_food_serving', [$this, 'getRecipeFoodServing']),
        ];
    }

    public function getRecipeFoodServing(Recipe $recipe, Food $food): float
    {
        return $this->recipeFoodRepository->getServingSize($recipe, $food);
    }
} 