<?php

namespace App\Controller\Back\Gym;

use App\Entity\Gym;
use App\Entity\Membership;
use App\Form\GymType;
use App\Form\MembershipType;
use App\Repository\MembershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Message\SmsMessage;

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

        return $this->render('back/membership/index.html.twig', [
            'memberships' => $membershipRepository->findAll(),
            'form' => $form->createView(),
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

        return $this->render('back/membership/new.html.twig', [
            'membership' => $membership,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_membership_show', methods: ['GET'])]
    public function show(Membership $membership): Response
    {
        $form = $this->createForm(MembershipType::class, $membership, ['disabled' => true]);

        return $this->render('back/membership/show.html.twig', [
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

        return $this->render('back/membership/edit.html.twig', [
            'membership' => $membership,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_membership_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Membership $membership,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $membership->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($membership);
            $entityManager->flush();

            // ✅ Envoi du SMS à un numéro fixe
            $sms = new SmsMessage(
                '+21658712875',
                "L'abonnement ID {$membership->getId()} a été supprimé avec succès."
            );
            $bus->dispatch($sms);

            $this->addFlash('success', 'Abonnement supprimé et SMS envoyé.');
        }

        return $this->redirectToRoute('app_membership_index');
    }

    #[Route('/statsm', name: 'app_membership_stats', methods: ['GET'], priority: 1)]
    public function stats(MembershipRepository $membershipRepository): Response
    {
        $memberships = $membershipRepository->findAll();
        $statusStats = [];

        foreach ($memberships as $membership) {
            $status = strtolower($membership->getStatus());
            $statusStats[$status] = ($statusStats[$status] ?? 0) + 1;
        }

        ksort($statusStats);

        return $this->render('back/membership/statsm.html.twig', [
            'statusStats' => $statusStats,
        ]);
    }

    #[Route('/{id}/pdf', name: 'app_membership_export_pdf', methods: ['GET'])]
    public function exportPdf(Membership $membership): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $html = $this->renderView('back/membership/membership_pdf.html.twig', [
            'membership' => $membership,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="membership_' . $membership->getId() . '.pdf"',
        ]);
    }
    
}
