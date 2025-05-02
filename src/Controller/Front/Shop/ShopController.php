<?php

namespace App\Controller\Front\Shop;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/shop')]
class ShopController extends AbstractController
{
    #[Route('/', name: 'shop_home')]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $query = $productRepository->createQueryBuilder('p')->getQuery();

        $products = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            8 // Items per page
        );

        return $this->render('front/shop/index.html.twig', [
            'products' => $products,
            'categories' => $categoryRepository->findAll(),
        ]);
    }
}
