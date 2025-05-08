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
    #[Route('/gyms', name: 'gym_front_list')]
    public function list(GymRepository $gymRepository, PaginatorInterface $paginator, Request $request): Response
    {
        // On prépare la requête (QueryBuilder ou DQL)
        $query = $gymRepository->createQueryBuilder('g')->getQuery();

        // On applique la pagination (6 gyms par page ici)
        $gyms = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('front/gym/front.html.twig', [
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
