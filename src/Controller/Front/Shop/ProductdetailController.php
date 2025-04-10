<?php

namespace App\Controller\Front\Shop;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductdetailController extends AbstractController
{
    #[Route('/shop/product/{id}', name: 'productdetail')]
    public function show(int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        return $this->render('Front/Shop/productdetail.html.twig', [
            'product' => $product,
        ]);
    }
}
