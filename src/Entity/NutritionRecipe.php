<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'nutrition_recipe')]
class NutritionRecipe
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Nutrition::class)]
    #[ORM\JoinColumn(name: 'nutrition_id', referencedColumnName: 'id', nullable: false)]
    private ?Nutrition $nutrition = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Recipe::class)]
    #[ORM\JoinColumn(name: 'recipe_id', referencedColumnName: 'id', nullable: false)]
    private ?Recipe $recipe = null;

    #[ORM\Column(type: 'string', length: 20, nullable: false)]
    private string $mealType = '';

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $serving = 1;

    public function __construct()
    {
    }

    public function getNutrition(): ?Nutrition
    {
        return $this->nutrition;
    }

    public function setNutrition(?Nutrition $nutrition): self
    {
        $this->nutrition = $nutrition;
        return $this;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): self
    {
        $this->recipe = $recipe;
        return $this;
    }

    public function getMealType(): string
    {
        return $this->mealType;
    }

    public function setMealType(string $mealType): self
    {
        $this->mealType = $mealType;
        return $this;
    }

    public function getServing(): int
    {
        return $this->serving;
    }

    public function setServing(int $serving): self
    {
        $this->serving = $serving;
        return $this;
    }
} 