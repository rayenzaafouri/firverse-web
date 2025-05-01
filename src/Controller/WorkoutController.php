<?php

namespace App\Controller;

use App\Entity\Workout;
use App\Entity\Exercice;
use App\Form\WorkoutType;
use App\Repository\WorkoutRepository;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WorkoutController extends AbstractController
{
    #[Route('/admin/workouts',name: 'app_workout_index_admin', methods: ['GET'])]
    public function index(WorkoutRepository $workoutRepository): Response
    {
        return $this->render('/back/workout/index.html.twig', [
            'workouts' => $workoutRepository->findAll(),
        ]);
    }


    //done
    #[Route('/admin/workout/new', name: 'app_workout_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        $exercices = $entityManager->getRepository(Exercice::class)->findAll();

        $workout = new Workout();
        $form = $this->createForm(WorkoutType::class, $workout);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($workout);
            $entityManager->flush();

            $this->addFlash('success_message',  "Workout created successfully : " . $workout->getLabel());


            return $this->redirectToRoute('app_workout_index_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('/back/workout/new.html.twig', [
            'workout' => $workout,
            'exercices' => $exercices,
            'form' => $form,
            
        ]);
    }
    
    #[Route('/admin/workout/{id}', name: 'app_workout_show', methods: ['GET'])]
    public function show(Workout $workout): Response
    {
        return $this->render('/back/workout/show.html.twig', [
            'workout' => $workout,
        ]);
    }

    #[Route('/admin/workout/{id}/edit', name: 'app_workout_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Workout $workout, EntityManagerInterface $entityManager): Response
    {

        $exercices = $entityManager->getRepository(Exercice::class)->findAll();




        $form = $this->createForm(WorkoutType::class, $workout);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success_message',  "Workout updated : " . $workout->getLabel());

            return $this->redirectToRoute('app_workout_index_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('/back/workout/edit.html.twig', [
            'workout' => $workout,
            'form' => $form,
            'exercices' => $exercices,
        ]);
    }

    #[Route('/admin/workout/{id}/delete', name: 'app_workout_delete', methods: ['POST'])]
    public function delete(Request $request, Workout $workout, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$workout->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($workout);
            $entityManager->flush();
            $this->addFlash('success_message',  "Workout deleted : " . $workout->getLabel());

        }




        return $this->redirectToRoute('app_workout_index_admin', [], Response::HTTP_SEE_OTHER);
    }


    // -------------------------------------------------------------
    // User methods
    // -------------------------------------------------------------

#[Route('/workout/{id}', name: 'app_workout_show', methods: ['GET'])]
public function showUser(Workout $workout, ExerciceRepository $exerciceRepository): Response
{
    $exerciceIds = array_map(function($exercice) {
        return $exercice['id'];
    }, json_decode($workout->getExercises(), true));

    $exercices = $exerciceRepository->findBy(['id' => $exerciceIds]);


    foreach ($exercices as $exercice) {
        $stepsObject = json_decode($exercice->getSteps());

        if (json_last_error() !== JSON_ERROR_NONE) {    
            $this->addFlash('error_message', $exercice->getTitle() . 'Failed to process exercise steps.');
        }

        $exercice->stepsObject = $stepsObject;



    }

    $orderedExercices = [];
    foreach ($exerciceIds as $id) {
        foreach ($exercices as $exercice) {

            if ($exercice->getId() === (int) $id) {
                $orderedExercices[] = $exercice;
                break;
            }
        }
    }



    // Render the template and pass the ordered exercices
    return $this->render('/front/workout/show.html.twig', [
        'workout' => $workout,
        'exercices' => $orderedExercices,
    ]);
}


    // -------------------------------------------------------------
    // Helper functions
    // -------------------------------------------------------------
    

    
}
