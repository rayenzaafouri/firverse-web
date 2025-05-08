<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

#[Route('/reclamation')]
final class ReclamationController extends AbstractController
{
    #[Route(name: 'app_reclamation_index', methods: ['GET'])]
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        $user = $this->getUser();
        $reclamations = $reclamationRepository->findBy(
            ['user' => $user],
        );
        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }
    #[Route("/admin", name: 'app_admin_reclamation_index', methods: ['GET'])]
    public function adminIndex(ReclamationRepository $reclamationRepository): Response
    {
        return $this->render('reclamation/index-admin.html.twig', [
            'reclamations' => $reclamationRepository->findAll(),
        ]);
    }
    #[Route('/admin/export/csv', name: 'app_admin_reclamation_export_csv', methods: ['GET'])]
    public function exportCsv(ReclamationRepository $repo): Response
    {
        $filename = 'reclamations_' . date('Ymd_His') . '.csv';
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['ID', 'Title', 'Description', 'Date', 'Status', 'User Email']);

        foreach ($repo->findAll() as $r) {
            fputcsv($handle, [
                $r->getId(),
                $r->getTitle(),
                $r->getDescription(),
                $r->getDateReclamation()->format('Y-m-d H:i:s'),
                $r->isStatus() ? 'Traité' : 'En attente',
                $r->getUser()->getEmail(),
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return new Response($content, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    #[Route('/stats', name: 'app_reclamation_stats', methods: ['GET'])]
    public function stats(ReclamationRepository $repo): Response
    {
        $qb = $repo->createQueryBuilder('r')
            ->select('r.status AS status, COUNT(r.id) AS count')
            ->groupBy('r.status');
        $results = $qb->getQuery()->getArrayResult();

        $labels = [];
        $data   = [];
        foreach ($results as $row) {
            $labels[] = $row['status'] ? 'Traité' : 'En attente';
            $data[]   = (int)$row['count'];
        }

        return $this->render('reclamation/stats.html.twig', [
            'labels' => json_encode($labels),
            'data'   => json_encode($data),
        ]);
    }
    #[Route('/{id}/traiter', name: 'app_reclamation_traiter', methods: ['POST'])]
    public function traiter(
        Reclamation $reclamation,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Vérification CSRF
        if ($this->isCsrfTokenValid('traiter' . $reclamation->getId(), $request->request->get('_token'))) {
            $reclamation->setStatus(true);
            $em->flush();
            $this->addFlash('success', 'Réclamation marquée comme traitée.');
        }

        return $this->redirectToRoute('app_admin_reclamation_index');
    }

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();
        $reclamation
            ->setStatus(false)
            ->setDateReclamation(new \DateTimeImmutable())
            ->setUser($this->getUser());
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($reclamation);
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }
}
