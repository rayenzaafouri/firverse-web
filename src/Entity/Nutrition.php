<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

use App\Repository\NutritionRepository;

#[ORM\Entity(repositoryClass: NutritionRepository::class)]
#[ORM\Table(name: 'nutrition')]
class Nutrition
{
    public const MEAL_TYPES = ['breakfast', 'lunch', 'dinner', 'snack'];

    private ?EntityManagerInterface $entityManager = null;

    public function setEntityManager(EntityManagerInterface $entityManager): self
    {
        $this->entityManager = $entityManager;
        return $this;
    }

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

    public function addFood(Food $food, string $mealType = '', int $serving = 1): self
    {
        if (!in_array($mealType, self::MEAL_TYPES) && $mealType !== '') {
            throw new \InvalidArgumentException('Invalid meal type');
        }

        // Check if the food already exists for this meal type
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('nf')
           ->from('App\Entity\NutritionFood', 'nf')
           ->where('nf.nutrition = :nutrition_id')
           ->andWhere('nf.food = :food_id')
           ->andWhere('nf.mealType = :meal_type')
           ->setParameter('nutrition_id', $this->getId())
           ->setParameter('food_id', $food->getId())
           ->setParameter('meal_type', $mealType);
        
        $existingNutritionFood = $qb->getQuery()->getOneOrNullResult();
        
        if (!$existingNutritionFood) {
            if (!$this->getFoods()->contains($food)) {
                $this->getFoods()->add($food);
            }
            
            // Create and persist the NutritionFood entity
            $nutritionFood = new NutritionFood();
            $nutritionFood->setNutrition($this);
            $nutritionFood->setFood($food);
            $nutritionFood->setMealType($mealType);
            $nutritionFood->setServing($serving);
            
            $this->entityManager->persist($nutritionFood);
            $this->entityManager->flush();
        }
        
        return $this;
    }

    public function removeFood(Food $food, string $mealType = ''): self
    {
        if (!in_array($mealType, self::MEAL_TYPES) && $mealType !== '') {
            throw new \InvalidArgumentException('Invalid meal type');
        }

        if (!$this->entityManager) {
            throw new \RuntimeException('EntityManager is not set. Call setEntityManager() first.');
        }

        $this->getFoods()->removeElement($food);
        
        // Remove the NutritionFood entity
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete('App\Entity\NutritionFood', 'nf')
           ->where('nf.nutrition = :nutrition_id')
           ->andWhere('nf.food = :food_id')
           ->andWhere('nf.mealType = :meal_type')
           ->setParameter('nutrition_id', $this->getId())
           ->setParameter('food_id', $food->getId())
           ->setParameter('meal_type', $mealType);
        
        $qb->getQuery()->execute();
        
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

    public function addRecipe(Recipe $recipe, string $mealType = '', int $serving = 1): self
    {
        if (!in_array($mealType, self::MEAL_TYPES) && $mealType !== '') {
            throw new \InvalidArgumentException('Invalid meal type');
        }

        // Check if a NutritionRecipe entity already exists for this recipe and meal type
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('nr')
           ->from(NutritionRecipe::class, 'nr')
           ->where('nr.nutrition = :nutrition_id')
           ->andWhere('nr.recipe = :recipe_id')
           ->andWhere('nr.mealType = :meal_type')
           ->setParameter('nutrition_id', $this->getId())
           ->setParameter('recipe_id', $recipe->getId())
           ->setParameter('meal_type', $mealType)
           ->setMaxResults(1);

        $existingNutritionRecipe = $qb->getQuery()->getOneOrNullResult();

        if (!$existingNutritionRecipe) {
            if (!$this->getRecipes()->contains($recipe)) {
                $this->getRecipes()->add($recipe);
            }
            
            // Create and persist the NutritionRecipe entity
            $nutritionRecipe = new NutritionRecipe();
            $nutritionRecipe->setNutrition($this);
            $nutritionRecipe->setRecipe($recipe);
            $nutritionRecipe->setMealType($mealType);
            $nutritionRecipe->setServing($serving);
            
            $this->entityManager->persist($nutritionRecipe);
            $this->entityManager->flush();
        }
        
        return $this;
    }

    public function removeRecipe(Recipe $recipe, string $mealType = ''): self
    {
        if (!in_array($mealType, self::MEAL_TYPES) && $mealType !== '') {
            throw new \InvalidArgumentException('Invalid meal type');
        }

        if (!$this->entityManager) {
            throw new \RuntimeException('EntityManager is not set. Call setEntityManager() first.');
        }

        $this->getRecipes()->removeElement($recipe);
        
        // Remove the NutritionRecipe entity
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete('App\Entity\NutritionRecipe', 'nr')
           ->where('nr.nutrition = :nutrition_id')
           ->andWhere('nr.recipe = :recipe_id')
           ->andWhere('nr.mealType = :meal_type')
           ->setParameter('nutrition_id', $this->getId())
           ->setParameter('recipe_id', $recipe->getId())
           ->setParameter('meal_type', $mealType);
        
        $qb->getQuery()->execute();
        
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

    public function updateFoodServing(Food $food, string $mealType, int $serving): self
    {
        if (!in_array($mealType, self::MEAL_TYPES)) {
            throw new \InvalidArgumentException('Invalid meal type');
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->update('App\Entity\NutritionFood', 'nf')
           ->set('nf.serving', ':serving')
           ->where('nf.nutrition = :nutrition_id')
           ->andWhere('nf.food = :food_id')
           ->andWhere('nf.mealType = :meal_type')
           ->setParameter('nutrition_id', $this->getId())
           ->setParameter('food_id', $food->getId())
           ->setParameter('meal_type', $mealType)
           ->setParameter('serving', $serving);
        
        $qb->getQuery()->execute();
        
        return $this;
    }

    public function updateRecipeServing(Recipe $recipe, string $mealType, int $serving): self
    {
        if (!in_array($mealType, self::MEAL_TYPES)) {
            throw new \InvalidArgumentException('Invalid meal type');
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->update('App\Entity\NutritionRecipe', 'nr')
           ->set('nr.serving', ':serving')
           ->where('nr.nutrition = :nutrition_id')
           ->andWhere('nr.recipe = :recipe_id')
           ->andWhere('nr.mealType = :meal_type')
           ->setParameter('nutrition_id', $this->getId())
           ->setParameter('recipe_id', $recipe->getId())
           ->setParameter('meal_type', $mealType)
           ->setParameter('serving', $serving);
        
        $qb->getQuery()->execute();
        
        return $this;
    }

    public function getFoodsByMealType(string $mealType): Collection
    {
        if (!in_array($mealType, self::MEAL_TYPES)) {
            throw new \InvalidArgumentException('Invalid meal type');
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('f')
           ->from(Food::class, 'f')
           ->join('App\Entity\NutritionFood', 'nf')
           ->where('nf.nutrition = :nutrition_id')
           ->andWhere('nf.mealType = :meal_type')
           ->andWhere('nf.food = f.id')
           ->setParameter('nutrition_id', $this->getId())
           ->setParameter('meal_type', $mealType);

        return new ArrayCollection($qb->getQuery()->getResult());
    }

    public function getRecipesByMealType(string $mealType): Collection
    {
        if (!in_array($mealType, self::MEAL_TYPES)) {
            throw new \InvalidArgumentException('Invalid meal type');
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('r')
           ->from(Recipe::class, 'r')
           ->join('App\Entity\NutritionRecipe', 'nr')
           ->where('nr.nutrition = :nutrition_id')
           ->andWhere('nr.mealType = :meal_type')
           ->andWhere('nr.recipe = r.id')
           ->setParameter('nutrition_id', $this->getId())
           ->setParameter('meal_type', $mealType);

        return new ArrayCollection($qb->getQuery()->getResult());
    }

    public function getNutritionFood(Food $food, string $mealType): ?NutritionFood
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('nf')
           ->from(NutritionFood::class, 'nf')
           ->where('nf.nutrition = :nutrition_id')
           ->andWhere('nf.food = :food_id')
           ->andWhere('nf.mealType = :meal_type')
           ->setParameter('nutrition_id', $this->getId())
           ->setParameter('food_id', $food->getId())
           ->setParameter('meal_type', $mealType)
           ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getNutritionRecipe(Recipe $recipe, string $mealType): ?NutritionRecipe
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('nr')
           ->from(NutritionRecipe::class, 'nr')
           ->where('nr.nutrition = :nutrition_id')
           ->andWhere('nr.recipe = :recipe_id')
           ->andWhere('nr.mealType = :meal_type')
           ->setParameter('nutrition_id', $this->getId())
           ->setParameter('recipe_id', $recipe->getId())
           ->setParameter('meal_type', $mealType)
           ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
