<?php
/*
Controls the initial page of the admin dashboard
*/

namespace App\Controller\Back\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminDashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        return $this->render('Back/admin/dashboard.html.twig', [
            'controller_name' => 'AdminDashboardController',
        ]);
    }
}