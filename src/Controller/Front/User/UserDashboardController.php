<?php

namespace App\Controller\Front\User;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserDashboardController extends AbstractController
{
    #[Route('/home', name: 'user_dashboard')]
    public function index(ProductRepository $productRepository): Response
    {
        $discountedProducts = $productRepository->findDiscountedProducts(); 
        return $this->render('/front/user/dashboard.html.twig', [
            'discountedProducts' => $discountedProducts,
        ]);
    }
}
