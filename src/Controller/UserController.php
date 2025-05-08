<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Component\Pager\PaginatorInterface;

final class UserController extends AbstractController
{
    #[Route('/admin/user', name: 'app_user_index', methods: ['GET'])]
    public function index(
        Request $request,
        UserRepository $userRepository,
        PaginatorInterface $paginator
    ): Response {
        $search    = $request->query->get('search', '');
        $sortField = $request->query->get('sortField', 'id');
        $sortDir   = $request->query->get('sortDir', 'asc') === 'desc' ? 'DESC' : 'ASC';
        $page      = $request->query->getInt('page', 1);
        $allowed   = ['id', 'first_name', 'email', 'phone', 'role', 'birth_date'];
        if (!in_array($sortField, $allowed)) {
            $sortField = 'id';
        }

        $qb = $userRepository->createQueryBuilder('u');
        if ($search !== '') {
            $qb->andWhere('u.first_name LIKE :s OR u.last_name LIKE :s OR u.email LIKE :s')
                ->setParameter('s', '%' . $search . '%');
        }
        $qb->orderBy('u.' . $sortField, $sortDir);

        $pagination = $paginator->paginate($qb, $page, 5);

        return $this->render('user/index.html.twig', [
            'pagination' => $pagination,
            'search'     => $search,
            'sortField'  => $sortField,
            'sortDir'    => strtolower($sortDir),
        ]);
    }

    #[Route('/list/html', name: 'app_user_list_html', methods: ['GET'])]
    public function listHtml(
        Request $request,
        UserRepository $userRepository,
        PaginatorInterface $paginator
    ): Response {
        $search    = $request->query->get('search', '');
        $sortField = $request->query->get('sortField', 'id');
        $sortDir   = $request->query->get('sortDir', 'asc') === 'desc' ? 'DESC' : 'ASC';
        $page      = $request->query->getInt('page', 1);
        $allowed   = ['id', 'first_name', 'email', 'phone', 'role', 'birth_date'];
        if (!in_array($sortField, $allowed)) {
            $sortField = 'id';
        }

        $qb = $userRepository->createQueryBuilder('u');
        if ($search !== '') {
            $qb->andWhere('u.first_name LIKE :s OR u.last_name LIKE :s OR u.email LIKE :s')
                ->setParameter('s', '%' . $search . '%');
        }
        $qb->orderBy('u.' . $sortField, $sortDir);

        $pagination = $paginator->paginate($qb, $page, 5);

        return $this->render('user/_list.html.twig', [
            'pagination' => $pagination,
            'search'     => $search,
            'sortField'  => $sortField,
            'sortDir'    => strtolower($sortDir),
        ]);
    }
    #[Route('/admin/user/list', name: 'app_user_list', methods: ['GET'])]
    public function list(Request $request, UserRepository $repo): JsonResponse
    {
        $search    = $request->query->get('search', '');
        $sortField = $request->query->get('sortField', 'id');
        $sortDir   = strtolower($request->query->get('sortDir', 'asc')) === 'desc' ? 'DESC' : 'ASC';

        $allowed = ['id', 'first_name', 'email', 'phone', 'role', 'birthDate'];
        if (!in_array($sortField, $allowed)) {
            $sortField = 'id';
        }

        $qb = $repo->createQueryBuilder('u');

        if ($search !== '') {
            $qb->andWhere('u.first_name LIKE :s OR u.last_name LIKE :s OR u.email LIKE :s')
                ->setParameter('s', '%' . $search . '%');
        }

        $qb->orderBy('u.' . $sortField, $sortDir);

        $users = $qb->getQuery()->getArrayResult();

        return $this->json($users);
    }
    #[Route('/admin/user/stats', name: 'app_user_stats', methods: ['GET'])]
    public function stats(UserRepository $userRepository): Response
    {
        $qb = $userRepository->createQueryBuilder('u')
            ->select('u.role AS label, COUNT(u.id) AS value')
            ->groupBy('u.role');
        $results = $qb->getQuery()->getArrayResult();

        $labels = array_column($results, 'label');
        $data   = array_column($results, 'value');

        return $this->render('user/stats.html.twig', [
            'labels' => json_encode($labels),
            'data'   => json_encode($data),
        ]);
    }
    #[Route('/admin/user/export/pdf', name: 'app_user_export_pdf', methods: ['GET'])]
    public function exportPdf(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        $logoPath       = $this->getParameter('kernel.project_dir') . '/public/fitverse-black.png';
        $avatarBasePath = $this->getParameter('kernel.project_dir') . '/public/profile_images/';
        $html = $this->renderView('user/export_pdf.html.twig', [
            'users'          => $users,
            'logoPath'       => $logoPath,
            'avatarBasePath' => $avatarBasePath,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $output = $dompdf->output();
        $filename = 'users_' . date('Ymd_His') . '.pdf';

        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('/user/profile/edit', name: 'app_user_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->remove('role');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData()) {
                $hashed = $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                );
                $user->setPassword($hashed);
            }
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('images_directory'), $newFilename);
                $user->setImage($newFilename);
            }
            $em->flush();

            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('app_reclamation_index');
        }
        return $this->render('user/edit_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/admin/user/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData()) {
                $hashed = $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                );
                $user->setPassword($hashed);
            }
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('images_directory'), $newFilename);
                $user->setImage($newFilename);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/admin/user/{id}/show', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/user/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->remove('password');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('images_directory'), $newFilename);
                $user->setImage($newFilename);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/admin/user/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
