<?php

namespace App\Controller\Front\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserDashboardController extends AbstractController
{
    #[Route('/home', name: 'user_dashboard')]
    public function index(): Response
    {
        return $this->render('/front/user/dashboard.html.twig');
    }
}
