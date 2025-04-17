<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Recherche des événements par un terme de recherche.
     *
     * @param string $searchTerm Le terme de recherche (par exemple, nom, emplacement ou date)
     * @return Event[] Retourne un tableau d'événements correspondant au terme de recherche
     */
    public function findBySearchTerm(string $searchTerm = ''): array
    {
        $queryBuilder = $this->createQueryBuilder('e');

        if (!empty($searchTerm)) {
            $queryBuilder->andWhere('LOWER(e.name) LIKE :search')
                         ->orWhere('LOWER(e.location) LIKE :search')
                         ->orWhere('LOWER(e.date) LIKE :search')
                         ->setParameter('search', '%' . strtolower($searchTerm) . '%');
        }

        return $queryBuilder->getQuery()->getResult();
    }

    // Exemple de méthode existante, tu peux les laisser commentées si tu n'en as pas besoin.
    /*
    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findOneBySomeField($value): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }
    */
}
