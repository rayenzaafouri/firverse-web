<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NavigationExampleController extends AbstractController
{
    #[Route('/diag')]
    public function index(UserRepository $userRepo): Response
    {
        return $this->render('navigation_example/index.html.twig', [
            'controller_name' => 'AuthentificationController',
        ]);
    }
}
