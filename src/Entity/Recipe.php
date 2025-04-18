<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\RecipeRepository;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[ORM\Table(name: 'recipes')]
class Recipe
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

    #[ORM\Column(type: 'string', nullable: false)]
#[Assert\NotBlank]
#[Assert\Length(min: 3, max: 40, minMessage: 'Recipe name must be at least {{ limit }} characters', maxMessage: 'Recipe name cannot be longer than {{ limit }} characters')]
private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'recipes')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $times_used = null;

    public function getTimes_used(): ?int
    {
        return $this->times_used;
    }

    public function setTimes_used(?int $times_used): self
    {
        $this->times_used = $times_used;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $is_favoris = null;

    public function is_favoris(): ?bool
    {
        return $this->is_favoris;
    }

    public function setIs_favoris(?bool $is_favoris): self
    {
        $this->is_favoris = $is_favoris;
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Nutrition::class, inversedBy: 'recipes')]
    #[ORM\JoinTable(
        name: 'nutrition_recipe',
        joinColumns: [
            new ORM\JoinColumn(name: 'recipe_id', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'nutrition_id', referencedColumnName: 'id')
        ]
    )]
    private Collection $nutritions;

    /**
     * @return Collection<int, Nutrition>
     */
    public function getNutritions(): Collection
    {
        if (!$this->nutritions instanceof Collection) {
            $this->nutritions = new ArrayCollection();
        }
        return $this->nutritions;
    }

    public function addNutrition(Nutrition $nutrition): self
    {
        if (!$this->getNutritions()->contains($nutrition)) {
            $this->getNutritions()->add($nutrition);
        }
        return $this;
    }

    public function removeNutrition(Nutrition $nutrition): self
    {
        $this->getNutritions()->removeElement($nutrition);
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Food::class, inversedBy: 'recipes')]
    #[ORM\JoinTable(
        name: 'recipe_food',
        joinColumns: [
            new ORM\JoinColumn(name: 'recipe_id', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'food_id', referencedColumnName: 'id')
        ]
    )]
    private Collection $foods;

    public function __construct()
    {
        $this->nutritions = new ArrayCollection();
        $this->foods = new ArrayCollection();
    }

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

    public function getTimesUsed(): ?int
    {
        return $this->times_used;
    }

    public function setTimesUsed(?int $times_used): static
    {
        $this->times_used = $times_used;

        return $this;
    }

    public function isFavoris(): ?bool
    {
        return $this->is_favoris;
    }

    public function setIsFavoris(?bool $is_favoris): static
    {
        $this->is_favoris = $is_favoris;

        return $this;
    }

}
