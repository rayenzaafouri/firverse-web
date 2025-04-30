<?php

namespace App\Controller\event\front;

use App\Entity\Participation;
use App\Form\ParticipationType;
use App\Repository\ParticipationRepository;
use App\Repository\EventRepository;
use Psr\Log\LoggerInterface; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\JsonResponse;
#[Route('/participation')]
final class ParticipationController extends AbstractController
{
    #[Route(name: 'app_participation_index', methods: ['GET'])]
    public function index(ParticipationRepository $participationRepository): Response
    {
        return $this->render('participation/index.html.twig', [
            'participations' => $participationRepository->findAll(),
        ]);
    }

    #[Route('/participation/new/{event_id}', name: 'app_participation_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        EventRepository $eventRepository,
        LoggerInterface $logger,
        int $event_id
    ): Response {
        $event = $eventRepository->find($event_id);
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $participation = new Participation();
        $participation->setEvent($event);

        $form = $this->createForm(ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($participation);
                $entityManager->flush();
                return $this->redirectToRoute('events_home');
            } else {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errorMsg = sprintf(
                        "Field: %s | Error: %s",
                        $error->getOrigin() ? $error->getOrigin()->getName() : 'global',
                        $error->getMessage()
                    );
                    $errors[] = $errorMsg;
                    $logger->error($errorMsg);
                    error_log($errorMsg); // You can remove this when you're done debugging
                }

                // Passing errors to the template
                return $this->render('participation/new.html.twig', [
                    'form' => $form->createView(),
                    'event' => $event,
                    'errors' => $errors, // Pass errors to the view
                ]);
            }
        }

        return $this->render('participation/new.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/{participationID}', name: 'app_participation_show', methods: ['GET'])]
    public function show(Participation $participation): Response
    {
        return $this->render('participation/show.html.twig', [
            'participation' => $participation,
        ]);
    }

    #[Route('/{participationID}/edit', name: 'app_participation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participation $participation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_participation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participation/edit.html.twig', [
            'participation' => $participation,
            'form' => $form,
        ]);
    }

    #[Route('/{participationID}', name: 'app_participation_delete', methods: ['POST'])]
    public function delete(Request $request, Participation $participation, EntityManagerInterface $entityManager): Response
    {
        // Correct CSRF token handling
        if ($this->isCsrfTokenValid('delete'.$participation->getParticipationID(), $request->request->get('_token'))) {
            $entityManager->remove($participation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_participation_index', [], Response::HTTP_SEE_OTHER);
    }

  
    #[Route('/send-email', name: 'send_email', methods: ['POST'])]
    public function sendEmail(Request $request, MailerInterface $mailer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $to = $data['to'] ?? null;
        $subject = $data['subject'] ?? 'Confirmation';
        $content = $data['content'] ?? 'Votre participation a été enregistrée avec succès.';
    
        if (!$to) {
            return new JsonResponse(['success' => false, 'error' => 'Adresse manquante'], 400);
        }
    
        $email = (new Email())
            ->from('miled5076@gmail.com')
            ->to($to)
            ->subject($subject)
            ->text($content);
    
        try {
            $mailer->send($email);
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }



    #[Route('/stats', name: 'stats')]
    public function showStats(ParticipationRepository $repo): Response
    {
        // Récupérer toutes les participations
        $participations = $repo->findAll();
    
        // Initialiser une variable pour la somme du nombre de participants
        $totalParticipants = 0;
    
        // Tableau pour stocker les données nécessaires pour les graphiques
        $data = [];
    
        // Itérer sur les participations pour collecter les informations nécessaires
        foreach ($participations as $p) {
            // Ajouter le nombre de participants à la somme totale
            $totalParticipants += $p->getNumberOfParticipants();
    
            // Ajouter les autres informations dans le tableau
            $data[] = [
                'gender' => $p->getGender(),
                'participantType' => $p->getParticipantType(),
                'numberOfParticipants' => $p->getNumberOfParticipants(),
            ];
        }
    
        // Passer les données à la vue
        return $this->render('stats/index.html.twig', [
            'participations' => $data,
            'totalParticipants' => $totalParticipants, // Ajouter la somme à la vue
        ]);
    }
    

}

