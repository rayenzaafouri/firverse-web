<?php
namespace App\RecommendationBundle\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RecommendationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return Product[]
     */
    public function findTopAlsoBought(Product $product, int $limit = 5): array
    {
        $dql = <<<DQL
SELECT p2 AS prod, COUNT(DISTINCT o.id) AS freq
FROM App\Entity\Product p2
JOIN p2.orderDetails od2
JOIN od2.order o
JOIN o.orderDetails od1
WHERE od1.product = :prod
  AND p2 != :prod
GROUP BY p2
ORDER BY freq DESC
DQL;

        $rows = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('prod', $product)
            ->setMaxResults($limit)
            ->getResult();

        return array_map(fn($r) => $r['prod'], $rows);
    }
}