<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NavigationExampleController extends AbstractController
{
    #[Route('/example', name: 'app_navigation_example')]
    public function index(): Response
    {
        return $this->render('navigation_example/index.html.twig', [
            'controller_name' => 'NavigationExampleController',
        ]);
    }
}
