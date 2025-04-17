<?php

namespace App\Controller\event\front;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Event;
use App\Form\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
class EventsHomeController extends AbstractController
{
    #[Route('/events/home', name: 'events_home')]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->render('/event/front/events_home/index.html.twig', [
            'eventsdata' => $events,
        ]);
    }

    #[Route('/event/{id}', name: 'event_detail')]
    public function showDetail(Event $event): Response
    {
        return $this->render('/event/front/events_home/detail.html.twig', [
            'event' => $event,
        ]);
    }

    

}