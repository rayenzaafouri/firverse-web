<?php

namespace App\Repository;

use App\Entity\Nutrition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;

/**
 * @extends ServiceEntityRepository<Nutrition>
 */
class NutritionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nutrition::class);
    }

    //    /**
    //     * @return Nutrition[] Returns an array of Nutrition objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Nutrition
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findByDateRange(int $userId, DateTime $startDate, DateTime $endDate): array
    {
        // Get all nutrition entries for the date range
        $nutritionEntries = $this->createQueryBuilder('n')
            ->where('n.user_id = :userId')
            ->andWhere('n.date BETWEEN :startDate AND :endDate')
            ->setParameter('userId', $userId)
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->orderBy('n.date', 'ASC')
            ->getQuery()
            ->getResult();

        // Process each day's data
        $result = [];
        foreach ($nutritionEntries as $nutrition) {
            $date = $nutrition->getDate()->format('Y-m-d');
            if (!isset($result[$date])) {
                $result[$date] = [
                    'date' => $nutrition->getDate(),
                    'total_calories' => 0,
                    'total_protein' => 0,
                    'total_carbs' => 0,
                    'total_fats' => 0
                ];
            }

            // Get nutrition foods for this nutrition entry
            $nutritionFoods = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('nf', 'f')
                ->from('App\Entity\NutritionFood', 'nf')
                ->join('nf.food', 'f')
                ->where('nf.nutrition = :nutrition')
                ->setParameter('nutrition', $nutrition)
                ->getQuery()
                ->getResult();

            // Calculate from foods
            foreach ($nutritionFoods as $nutritionFood) {
                $food = $nutritionFood->getFood();
                $serving = $nutritionFood->getServing();
                
                $result[$date]['total_calories'] += $food->getCalories() * $serving;
                $result[$date]['total_protein'] += $food->getProtein() * $serving;
                $result[$date]['total_carbs'] += $food->getCarbohydrate() * $serving;
                $result[$date]['total_fats'] += $food->getFats() * $serving;
            }

            // Get nutrition recipes for this nutrition entry
            $nutritionRecipes = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('nr', 'r')
                ->from('App\Entity\NutritionRecipe', 'nr')
                ->join('nr.recipe', 'r')
                ->where('nr.nutrition = :nutrition')
                ->setParameter('nutrition', $nutrition)
                ->getQuery()
                ->getResult();

            // Calculate from recipes
            foreach ($nutritionRecipes as $nutritionRecipe) {
                $recipe = $nutritionRecipe->getRecipe();
                $serving = $nutritionRecipe->getServing();
                
                $result[$date]['total_calories'] += $recipe->getTotalCalories() * $serving;
                $result[$date]['total_protein'] += $recipe->getTotalProtein() * $serving;
                $result[$date]['total_carbs'] += $recipe->getTotalCarbohydrate() * $serving;
                $result[$date]['total_fats'] += $recipe->getTotalFats() * $serving;
            }
        }

        // Convert to array and sort by date
        return array_values($result);
    }
}
