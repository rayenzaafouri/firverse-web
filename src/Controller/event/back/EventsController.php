<?php

namespace App\Controller\event\back;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\FormError;
use App\Entity\Event;
use App\Form\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
class EventsController extends AbstractController
{
    

    #[Route('/events/new', name: 'app_event_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
{
    $event = new Event();
    $form = $this->createForm(EventType::class, $event);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        // Validate the whole object
        $errors = $validator->validate($event);
        foreach ($errors as $error) {
            $propertyPath = $error->getPropertyPath();
            if ($form->has($propertyPath)) {
                $form->get($propertyPath)->addError(new FormError($error->getMessage()));
            }
        }
    
        if ($form->isValid()) {
            // Handle valid form submission
            $photoFile = $form->get('photo')->getData();
    
            if ($photoFile) {
                $newFilename = uniqid() . '.' . $photoFile->guessExtension();
    
                try {
                    $photoFile->move(
                        $this->getParameter('events_directory'),
                        $newFilename
                    );
                    $event->setPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'There was an error uploading your photo');
                }
            }
    
            $entityManager->persist($event);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_event_index');
        } else {
            // Log form-level errors
            foreach ($form->getErrors(true) as $error) {
                error_log('Form Error: ' . $error->getMessage());
            }
        }
    }
    

    return $this->render('event/back/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route('/events', name: 'app_event_index', methods: ['GET'])]
    public function indexback(EventRepository $eventRepository): Response
    {
        // Get all events ordered by date (newest first)
        $events = $eventRepository->findBy([], ['date' => 'ASC']);

        return $this->render('event/back/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('event/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/back/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/events/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Event $event,
        EntityManagerInterface $entityManager
    ): Response {
        // Get the parameter from the service container
        $eventsDirectory = $this->getParameter('events_directory');
        
        // Store the current photo filename before handling the form
        $currentPhoto = $event->getPhoto();
        
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $photoFile */
            $photoFile = $form->get('photo')->getData();
            
            // Handle file upload if a new photo was submitted
            if ($photoFile) {
                $newFilename = uniqid().'.'.$photoFile->guessExtension();
                
                try {
                    // Move the uploaded file
                    $photoFile->move(
                        $eventsDirectory,
                        $newFilename
                    );
                    
                    // Update the entity with new filename
                    $event->setPhoto($newFilename);
                    
                    // Delete the old photo file if it exists
                    if ($currentPhoto && file_exists($eventsDirectory.'/'.$currentPhoto)) {
                        unlink($eventsDirectory.'/'.$currentPhoto);
                    }
                } catch (FileException $e) {
                    $this->addFlash('error', 'There was an error uploading your photo');
                    return $this->redirectToRoute('app_event_edit', ['id' => $event->getId()]);
                }
            } else {
                // If no new photo uploaded, keep the existing one
                $event->setPhoto($currentPhoto);
            }
    
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Event updated successfully!');
                return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'There was an error saving your changes');
                return $this->redirectToRoute('app_event_edit', ['id' => $event->getId()]);
            }
        }
    
        return $this->render('event/back/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('event/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

}