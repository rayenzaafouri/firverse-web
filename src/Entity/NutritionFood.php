<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'nutrition_food')]
class NutritionFood
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Nutrition::class)]
    #[ORM\JoinColumn(name: 'nutrition_id', referencedColumnName: 'id', nullable: false)]
    private ?Nutrition $nutrition = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Food::class)]
    #[ORM\JoinColumn(name: 'food_id', referencedColumnName: 'id', nullable: false)]
    private ?Food $food = null;

    #[ORM\Column(type: 'string', length: 20, nullable: false)]
    private string $mealType;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $serving = 1;

    public function __construct()
    {
        $this->mealType = '';
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

    public function getFood(): ?Food
    {
        return $this->food;
    }

    public function setFood(?Food $food): self
    {
        $this->food = $food;
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