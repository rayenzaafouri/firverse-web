<?php

namespace App\Controller;

use App\Entity\Membership;
use App\Form\MembershipType;
use App\Repository\MembershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Gym;
use App\Form\GymType;

#[Route('/membership')]
final class MembershipController extends AbstractController
{
    #[Route(name: 'app_membership_index', methods: ['GET', 'POST'])]
    public function index(Request $request, MembershipRepository $membershipRepository, EntityManagerInterface $entityManager): Response
    {
        $gym = new Gym();
        $form = $this->createForm(GymType::class, $gym);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($gym);
            $entityManager->flush();

            $this->addFlash('success', 'Salle de sport ajoutée avec succès.');

            return $this->redirectToRoute('app_membership_index');
        }

        return $this->render('membership/index.html.twig', [
            'memberships' => $membershipRepository->findAll(),
            'form' => $form->createView(), // 🔥 c'est ce qui manquait
        ]);
    }


    #[Route('/new', name: 'app_membership_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $membership = new Membership();
        $form = $this->createForm(MembershipType::class, $membership);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($membership);
            $entityManager->flush();

            return $this->redirectToRoute('app_membership_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('membership/new.html.twig', [
            'membership' => $membership,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_membership_show', methods: ['GET'])]
    public function show(Membership $membership): Response
    {
        $form = $this->createForm(MembershipType::class, $membership, [
            'disabled' => true,
        ]);

        return $this->render('membership/show.html.twig', [
            'form' => $form,
            'membership' => $membership,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_membership_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Membership $membership, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MembershipType::class, $membership);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_membership_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('membership/edit.html.twig', [
            'membership' => $membership,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_membership_delete', methods: ['POST'])]
    public function delete(Request $request, Membership $membership, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$membership->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($membership);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_membership_index', [], Response::HTTP_SEE_OTHER);
    }
}
