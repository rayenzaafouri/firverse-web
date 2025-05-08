<?php

namespace App\Repository;

use App\Entity\Waterconsumption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;

/**
 * @extends ServiceEntityRepository<Waterconsumption>
 */
class WaterconsumptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Waterconsumption::class);
    }

    /**
     * @param int $userId
     * @return Waterconsumption[] Returns an array of Waterconsumption objects for the last 10 days (including today), ordered by date ascending.
     */
    public function findLast10DaysForUser(int $userId): array
    {
        $today = new \DateTime();
        $startDate = (clone $today)->modify('-9 days')->setTime(0,0,0);
        $endDate = (clone $today)->setTime(23,59,59);
        return $this->createQueryBuilder('w')
            ->andWhere('w.user = :userId')
            ->andWhere('w.ConsumptionDate BETWEEN :start AND :end')
            ->setParameter('userId', $userId)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('w.ConsumptionDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByDateRange(int $userId, DateTime $startDate, DateTime $endDate): array
    {
        $qb = $this->createQueryBuilder('w')
            ->select('w.ConsumptionDate, SUM(w.AmountConsumed) as amount')
            ->where('w.user = :userId')
            ->andWhere('w.ConsumptionDate BETWEEN :startDate AND :endDate')
            ->setParameter('userId', $userId)
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->groupBy('w.ConsumptionDate')
            ->orderBy('w.ConsumptionDate', 'ASC');

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Waterconsumption[] Returns an array of Waterconsumption objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Waterconsumption
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
