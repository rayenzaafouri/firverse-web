<?php

namespace App\Entity;

use App\Repository\RecipeFoodRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeFoodRepository::class)]
#[ORM\Table(name: 'recipe_food')]
class RecipeFood
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private ?int $recipe_id = null;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private ?int $food_id = null;

    #[ORM\Column(name: 'servings', type: 'float', options: ['default' => 1.0])]
    private float $servings = 1.0;

    #[ORM\ManyToOne(targetEntity: Recipe::class)]
    #[ORM\JoinColumn(name: 'recipe_id', referencedColumnName: 'id')]
    private ?Recipe $recipe = null;

    #[ORM\ManyToOne(targetEntity: Food::class)]
    #[ORM\JoinColumn(name: 'food_id', referencedColumnName: 'id')]
    private ?Food $food = null;

    public function getRecipeId(): ?int
    {
        return $this->recipe_id;
    }

    public function setRecipeId(?int $recipe_id): self
    {
        $this->recipe_id = $recipe_id;
        return $this;
    }

    public function getFoodId(): ?int
    {
        return $this->food_id;
    }

    public function setFoodId(?int $food_id): self
    {
        $this->food_id = $food_id;
        return $this;
    }

    public function getServings(): float
    {
        return $this->servings;
    }

    public function setServings(float $servings): self
    {
        $this->servings = $servings;
        return $this;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): self
    {
        $this->recipe = $recipe;
        if ($recipe) {
            $this->recipe_id = $recipe->getId();
        }
        return $this;
    }

    public function getFood(): ?Food
    {
        return $this->food;
    }

    public function setFood(?Food $food): self
    {
        $this->food = $food;
        if ($food) {
            $this->food_id = $food->getId();
        }
        return $this;
    }
} 