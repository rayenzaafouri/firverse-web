<?php

namespace App\Controller\Back\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_home')]
    public function index(): Response
    {
        return $this->render('Back/Shop/admin.html.twig');
    }
}
