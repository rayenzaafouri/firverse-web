<?php
namespace App\RecommendationBundle\Controller;

use App\Entity\Product;
use App\RecommendationBundle\Service\RecommendationEngine;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/recommend', name: 'api_recommend_')]
class RecommendationController extends AbstractController
{
    public function __construct(private RecommendationEngine $engine) {}

    #[Route('/product/{id}', name: 'product', methods: ['GET'])]
    public function byProduct(Product $product): JsonResponse
    {
        $recs = $this->engine->forProduct($product, 6);

        $data = array_map(fn(Product $p) => [
            'id'    => $p->getId(),
            'name'  => $p->getName(),
            'price' => $p->getPrice(),
            'image' => $p->getImageUrl(),
            'url'   => $this->generateUrl('productdetail', ['id' => $p->getId()]),
        ], $recs);

        return new JsonResponse($data);
    }
}
