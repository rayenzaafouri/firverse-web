<?php

namespace App\Repository;

use App\Entity\Participation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participation>
 */
class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }

    // Example: A custom query to find participations by a specific field, e.g., 'event' or 'gender'.
    public function findByGender($gender): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.gender = :gender')
            ->setParameter('gender', $gender)
            ->orderBy('p.id', 'ASC') // Optionally, order results by ID
            ->getQuery()
            ->getResult();
    }

    // Example: Find one participation based on a specific field, e.g., 'email'.
    public function findOneByEmail($email): ?Participation
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
