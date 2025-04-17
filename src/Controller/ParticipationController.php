<?php

namespace App\Controller;

use App\Entity\Participation;
use App\Form\ParticipationType;
use App\Repository\ParticipationRepository;
use App\Repository\EventRepository;
use Psr\Log\LoggerInterface;  // Add this line

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
                return $this->redirectToRoute('app_participation_index');
            } else {
                // Log all validation errors
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errorMsg = sprintf(
                        "Field: %s | Error: %s",
                        $error->getOrigin() ? $error->getOrigin()->getName() : 'global',
                        $error->getMessage()
                    );
                    $errors[] = $errorMsg;
                    
                    // Log to Symfony logs
                    $logger->error($errorMsg);
                    
                    // Output to terminal (STDOUT)
                    error_log($errorMsg);
                }
    
                // Debug dump (visible in profiler)
                dump('Validation Errors:', $errors);
    
                $this->addFlash('error', 'Please fix the validation errors');
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
        if ($this->isCsrfTokenValid('delete'.$participation->getParticipationID(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($participation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_participation_index', [], Response::HTTP_SEE_OTHER);
    }
}
