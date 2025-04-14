<?php

namespace App\Controller;

use App\Repository\GymRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GymFrontController extends AbstractController
{
    #[Route('/gyms', name: 'gym_front_list')]
    public function list(GymRepository $gymRepository): Response
    {
        $gyms = $gymRepository->findAll();
        return $this->render('gym_front/front.html.twig', [
            'gyms' => $gyms,
        ]);
    }
    #[Route('/gyms/{id}', name: 'gym_front_detail')]
    public function detail(int $id, GymRepository $gymRepository): Response
    {
        $gym = $gymRepository->find($id);

        if (!$gym) {
            throw $this->createNotFoundException('Salle non trouvée.');
        }

        return $this->render('gym_front/detail.html.twig', [
            'gym' => $gym,
        ]);
    }

}
