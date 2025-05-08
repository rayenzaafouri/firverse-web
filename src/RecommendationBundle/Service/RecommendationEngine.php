<?php
namespace App\RecommendationBundle\Service;

use App\Entity\Product;
use App\RecommendationBundle\Repository\RecommendationRepository;

class RecommendationEngine
{
    public function __construct(private RecommendationRepository $repo) {}

    /**
     * @return Product[]
     */
    public function forProduct(Product $product, int $limit = 5): array
    {
        return $this->repo->findTopAlsoBought($product, $limit);
    }
}
