<?php

namespace App\Controller\Front\Shop;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/shop')]
class ShopController extends AbstractController
{
    #[Route('/', name: 'shop_home')]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        return $this->render('front/shop/index.html.twig', [
            'products' => $productRepository->findAll(),
            'categories' => $categoryRepository->findAll(),

        ]);
    }
}
