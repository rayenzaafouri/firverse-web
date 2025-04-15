<?php

namespace App\Controller\Front\Shop;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/my-orders')]
class UserOrderController extends AbstractController
{
    #[Route(name: 'user_orders', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'You must be logged in to view your orders.');
            return $this->redirectToRoute('app_login'); // Adjust login route if different
        }

        $orders = $orderRepository->findBy(['user' => $user], ['orderDate' => 'DESC']);

        return $this->render('Front/Shop/user_orders.html.twig', [
            'orders' => $orders,
        ]);
    }
}
