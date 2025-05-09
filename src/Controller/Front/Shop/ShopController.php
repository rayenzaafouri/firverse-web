<?php

namespace App\Controller\Front\Shop;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
<<<<<<< HEAD
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
=======
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
>>>>>>> shop

#[Route('/shop')]
class ShopController extends AbstractController
{
    #[Route('/', name: 'shop_home')]
<<<<<<< HEAD
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        return $this->render('front/shop/index.html.twig', [
            'products' => $productRepository->findAll(),
            'categories' => $categoryRepository->findAll(),

        ]);
    }
}
=======
public function index(
    ProductRepository $productRepository,
    CategoryRepository $categoryRepository,
    Request $request,
    PaginatorInterface $paginator
): Response {
    $categoryName = $request->query->get('category');

    $qb = $productRepository->createQueryBuilder('p');

    if ($categoryName) {
        $qb->join('p.category', 'c')
           ->andWhere('c.name = :category')
           ->setParameter('category', $categoryName);
    }

    $query = $qb->getQuery();

    $products = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        8
    );

    return $this->render('front/shop/index.html.twig', [
        'products' => $products,
        'categories' => $categoryRepository->findAll(),
        'currentCategory' => $categoryName,
    ]);
}
}
>>>>>>> shop
