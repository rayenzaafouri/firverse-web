<?php

namespace App\Controller\Front\Gym;

use App\Repository\GymRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Knp\Component\Pager\PaginatorInterface;

class GymFrontController extends AbstractController
{
    #[Route('/gyms', name: 'gym_front')]
    public function index(Request $request, GymRepository $gymRepository, PaginatorInterface $paginator): Response
    {
        // Get the query builder for gyms
        $queryBuilder = $gymRepository->createQueryBuilder('g');

        // Paginate the result
        $gyms = $paginator->paginate(
            $queryBuilder, // Pass the query builder directly here
            $request->query->getInt('page', 1), // Get the current page from the query params
            6 // The number of items per page
        );

        return $this->render('front/gym/front.html.twig', [
            'gyms' => $gyms, // Pass paginated gyms object to Twig
        ]);
    }


    #[Route('/gyms/{id}', name: 'gym_front_detail')]
    public function detail(int $id, GymRepository $gymRepository): Response
    {
        $gym = $gymRepository->find($id);

        if (!$gym) {
            throw $this->createNotFoundException('Salle non trouvée.');
        }

        return $this->render('front/gym/detail.html.twig', [
            'gym' => $gym,
        ]);
    }

    #[Route('/gym/{id}/demande-rejoindre', name: 'gym_demande_rejoindre', methods: ['POST'])]
    public function demandeRejoindre(int $id, GymRepository $gymRepository, MailerInterface $mailer): Response
    {
        $destinataireEmail = 'mouhamedali.tlili@esprit.tn';

        $email = (new Email())
            ->from('noreply@esprit.tn')
            ->to($destinataireEmail)
            ->subject('Demande de rejoindre une salle de sport')
            ->text('Bonjour, un utilisateur souhaite rejoindre une salle de sport sur FitVerse.');

        $mailer->send($email);
        $this->addFlash('success', 'Votre demande a bien été envoyée.');
        return $this->redirectToRoute('gym_front_detail', ['id' => $id]);
    }
}
