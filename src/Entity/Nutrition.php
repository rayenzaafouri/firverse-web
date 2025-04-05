<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\NutritionRepository;

#[ORM\Entity(repositoryClass: NutritionRepository::class)]
#[ORM\Table(name: 'nutrition')]
class Nutrition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date = null;

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $user_id = null;

    public function getUser_id(): ?int
    {
        return $this->user_id;
    }

    public function setUser_id(int $user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Food::class, inversedBy: 'nutritions')]
    #[ORM\JoinTable(
        name: 'nutrition_food',
        joinColumns: [
            new ORM\JoinColumn(name: 'nutrition_id', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'food_id', referencedColumnName: 'id')
        ]
    )]
    private Collection $foods;

    /**
     * @return Collection<int, Food>
     */
    public function getFoods(): Collection
    {
        if (!$this->foods instanceof Collection) {
            $this->foods = new ArrayCollection();
        }
        return $this->foods;
    }

    public function addFood(Food $food): self
    {
        if (!$this->getFoods()->contains($food)) {
            $this->getFoods()->add($food);
        }
        return $this;
    }

    public function removeFood(Food $food): self
    {
        $this->getFoods()->removeElement($food);
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Recipe::class, inversedBy: 'nutritions')]
    #[ORM\JoinTable(
        name: 'nutrition_recipe',
        joinColumns: [
            new ORM\JoinColumn(name: 'nutrition_id', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'recipe_id', referencedColumnName: 'id')
        ]
    )]
    private Collection $recipes;

    public function __construct()
    {
        $this->foods = new ArrayCollection();
        $this->recipes = new ArrayCollection();
    }

    /**
     * @return Collection<int, Recipe>
     */
    public function getRecipes(): Collection
    {
        if (!$this->recipes instanceof Collection) {
            $this->recipes = new ArrayCollection();
        }
        return $this->recipes;
    }

    public function addRecipe(Recipe $recipe): self
    {
        if (!$this->getRecipes()->contains($recipe)) {
            $this->getRecipes()->add($recipe);
        }
        return $this;
    }

    public function removeRecipe(Recipe $recipe): self
    {
        $this->getRecipes()->removeElement($recipe);
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

}
