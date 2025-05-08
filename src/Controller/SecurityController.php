<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends AbstractController
{

    #[Route('/auth', name: 'app_auth', methods: ['GET'])]
    public function loginPage(AuthenticationUtils $authUtils): Response
    {
        $error        = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        $registrationForm = $this->createForm(RegistrationFormType::class);

        return $this->render('authentification/index.html.twig', [
            'last_username'     => $lastUsername,
            'error'             => $error,
            'registrationForm' => $registrationForm->createView(),
            'activeTab'         => 'login',
        ]);
    }

    #[Route('/auth/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('images_directory'), $newFilename);
                $user->setImage($newFilename);
            }
            $user->setRole("UTILISATEUR");

            $user->setPassword(
                $hasher->hashPassword($user, $form->get('plainPassword')->getData())
            );
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_auth');
        }

        return $this->render('authentification/index.html.twig', [
            'last_username'     => '',
            'error'             => null,
            'registrationForm' => $form->createView(),
            'activeTab'         => 'register',
        ]);
    }

    #[Route('/auth/login_check', name: 'app_auth_login', methods: ['POST'])]
    public function loginCheck(): void {}

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}