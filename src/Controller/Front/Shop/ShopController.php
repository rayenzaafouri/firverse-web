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
    $products = $productRepository->findAll();
    $today = new \DateTime();

    $productsWithDiscount = [];

    foreach ($products as $product) {
        $discountedPrice = null;
        $onSale = false;

        if ($product->getProductDiscounts()->count() > 0) {
            foreach ($product->getProductDiscounts() as $discount) {
                if ($discount->getValid_from() <= $today && $discount->getValid_until() >= $today) {
                    $onSale = true;
                    $discountedPrice = $product->getPrice() * (1 - ($discount->getDiscountPercentage() / 100));
                    break; // Only first valid discount is enough
                }
            }
        }

        $productsWithDiscount[] = [
            'product' => $product,
            'onSale' => $onSale,
            'discountedPrice' => $discountedPrice,
        ];
    }

    return $this->render('Front/Shop/index.html.twig', [
        'products' => $productsWithDiscount,
        'categories' => $categoryRepository->findAll(),
    ]);
}
}
