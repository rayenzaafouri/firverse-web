<?php

namespace App\Controller;

use App\Entity\Exercice;
use App\Form\ExerciceType;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExerciceController extends AbstractController
{


    //User methods :

    #[Route('/exercise/{id}', name: 'user_exercice_show', methods: ['GET'])]
    public function show(Exercice $exercice): Response
    {
        return $this->render('/front/exercise/show.html.twig', [
            'exercice' => $exercice,
        ]);
    }


    #[Route('/exercises',name: 'app_exercice_index', methods: ['GET'])]
    public function userIndex(ExerciceRepository $exerciceRepository): Response
    {
        return $this->render('front/exercise/showAll.html.twig', [
            'exercices' => $exerciceRepository->findAll(),
        ]);
    }



    // Admin methods :
    #[Route('/admin/exercises',name: 'app_exercice_index_admin', methods: ['GET'])]
    public function adminIndex(ExerciceRepository $exerciceRepository): Response
    {
        return $this->render('back/admin/showAll.html.twig', [
            'exercices' => $exerciceRepository->findAll(),
        ]);
    }

    #[Route('/admin/exercise/new', name: 'app_exercice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $exercice = new Exercice(); 
        $form = $this->createForm(ExerciceType::class, $exercice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($exercice);
            $entityManager->flush();

            $this->addFlash('success_message', $exercice->getTitle() . ' created successfully');

            return $this->redirectToRoute('app_exercice_index_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('/back/exercise/new.html.twig', [
            'exercice' => $exercice,
            'form' => $form,
        ]);
    }




    #[Route('/admin/exercise/{id}/', name: 'app_exercice_show_admin', methods: ['GET'])]
    public function showAdmin(Exercice $exercice): Response
    {

        
        return $this->render('/back/exercise/show.html.twig', [
            'exercice' => $exercice,

        ]);
    }

    #[Route('/admin/exercise/{id}/edit', name: 'app_exercice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Exercice $exercice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ExerciceType::class, $exercice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success_message',  $exercice->getTitle() . ' updated successfully');

            return $this->redirectToRoute('app_exercice_index_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('/back/exercise/edit.html.twig', [
            'exercice' => $exercice,
            'form' => $form,
        ]);


    }

    #[Route('/admin/exercise/{id}/delete', name: 'app_exercice_delete', methods: ['POST'])]
    public function delete(Request $request, Exercice $exercice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$exercice->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($exercice);
            $entityManager->flush();
            $this->addFlash('success_message', $exercice->getTitle() . ' deleted successfully');
        }

        return $this->redirectToRoute('app_exercice_index_admin', ["submit"=>"success"], Response::HTTP_SEE_OTHER);
    }

    public function resolveEquipmentId($id){
        $equipment = array(
            "0" => "Barbell",
            "1" => "Dumbbells",
            "2" => "Bodyweight",
            "3" => "Machine",
            "4" => "Medicine Ball",
            "5" => "Kettlebells",
            "6" => "Stretches",
            "7" => "Cables",
            "8" => "Band",
            "9" => "Plate",
            "10" => "TRX",
            "11" => "Yoga",
            "12" => "Bosu Ball",
            "13" => "Vitruvian",
            "14" => "Cardio",
            "15" => "Smith Machine",
            "16" => "Recovery"
        );
        return isset($equipment[$id]) ? $equipment[$id] : null;
    }
}
