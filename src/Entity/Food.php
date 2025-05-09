<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\FoodRepository;

#[ORM\Entity(repositoryClass: FoodRepository::class)]
#[ORM\Table(name: 'food')]
class Food
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
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
#[Assert\NotBlank]
#[Assert\Regex(pattern: '/^[\p{L} ]+$/u', message: 'The name should contain only letters and spaces.')]
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

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $measure = null;

    public function getMeasure(): ?string
    {
        return $this->measure;
    }

    public function setMeasure(?string $measure): self
    {
        $this->measure = $measure;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $grams = null;

    public function getGrams(): ?float
    {
        return $this->grams;
    }

    public function setGrams(?float $grams): self
    {
        $this->grams = $grams;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
#[Assert\NotBlank]
#[Assert\Type(type: 'numeric', message: 'Calories must be a number.')]
private ?float $calories = null;

    public function getCalories(): ?float
    {
        return $this->calories;
    }

    public function setCalories(?float $calories): self
    {
        $this->calories = $calories;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
#[Assert\NotBlank]
#[Assert\Type(type: 'numeric', message: 'Protein must be a number.')]
private ?float $protein = null;

    public function getProtein(): ?float
    {
        return $this->protein;
    }

    public function setProtein(?float $protein): self
    {
        $this->protein = $protein;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
#[Assert\NotBlank]
#[Assert\Type(type: 'numeric', message: 'Fats must be a number.')]
private ?float $fats = null;

    public function getFats(): ?float
    {
        return $this->fats;
    }

    public function setFats(?float $fats): self
    {
        $this->fats = $fats;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true, name: 'saturatedFats')]
#[Assert\NotBlank]
#[Assert\Type(type: 'numeric', message: 'Saturated Fats must be a number.')]
private ?float $saturatedFats = null;

    public function getSaturatedFats(): ?float
    {
        return $this->saturatedFats;
    }

    public function setSaturatedFats(?float $saturatedFats): self
    {
        $this->saturatedFats = $saturatedFats;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
#[Assert\NotBlank]
#[Assert\Type(type: 'numeric', message: 'Fibre must be a number.')]
private ?float $fibre = null;

    public function getFibre(): ?float
    {
        return $this->fibre;
    }

    public function setFibre(?float $fibre): self
    {
        $this->fibre = $fibre;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
#[Assert\NotBlank]
#[Assert\Type(type: 'numeric', message: 'Carbohydrate must be a number.')]
private ?float $carbohydrate = null;

    public function getCarbohydrate(): ?float
    {
        return $this->carbohydrate;
    }

    public function setCarbohydrate(?float $carbohydrate): self
    {
        $this->carbohydrate = $carbohydrate;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
#[Assert\NotBlank]
#[Assert\Type(type: 'numeric', message: 'Sugar must be a number.')]
private ?float $sugar = null;

    public function getSugar(): ?float
    {
        return $this->sugar;
    }

    public function setSugar(?float $sugar): self
    {
        $this->sugar = $sugar;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
#[Assert\NotBlank]
#[Assert\Type(type: 'numeric', message: 'Cholesterol must be a number.')]
private ?float $cholesterol = null;

    public function getCholesterol(): ?float
    {
        return $this->cholesterol;
    }

    public function setCholesterol(?float $cholesterol): self
    {
        $this->cholesterol = $cholesterol;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
#[Assert\NotBlank]
#[Assert\Type(type: 'numeric', message: 'Sodium must be a number.')]
private ?float $sodium = null;

    public function getSodium(): ?float
    {
        return $this->sodium;
    }

    public function setSodium(?float $sodium): self
    {
        $this->sodium = $sodium;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
#[Assert\NotBlank]
#[Assert\Type(type: 'numeric', message: 'Magnesium must be a number.')]
private ?float $magnesium = null;

    public function getMagnesium(): ?float
    {
        return $this->magnesium;
    }

    public function setMagnesium(?float $magnesium): self
    {
        $this->magnesium = $magnesium;
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

    #[ORM\ManyToMany(targetEntity: Nutrition::class, inversedBy: 'foods')]
    #[ORM\JoinTable(
        name: 'nutrition_food',
        joinColumns: [
            new ORM\JoinColumn(name: 'food_id', referencedColumnName: 'id')
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

    #[ORM\ManyToMany(targetEntity: Recipe::class, inversedBy: 'foods')]
    #[ORM\JoinTable(
        name: 'recipe_food',
        joinColumns: [
            new ORM\JoinColumn(name: 'food_id', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'recipe_id', referencedColumnName: 'id')
        ]
    )]
    private Collection $recipes;

    public function __construct()
    {
        $this->nutritions = new ArrayCollection();
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

    public function getTimesUsed(): ?int
    {
        return $this->times_used;
    }

    public function setTimesUsed(?int $times_used): static
    {
        $this->times_used = $times_used;

        return $this;
    }

}
