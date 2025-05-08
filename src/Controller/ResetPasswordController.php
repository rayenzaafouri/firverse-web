<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Utilisateur;
use App\Form\ForgotPasswordRequestFormType;
use App\Form\ResetPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordController extends AbstractController
{
    private const TTL = 3600; // one hour

    #[Route('/auth/forgot-password', name: 'app_forgot_password')]
    public function request(
        Request $request,
        ManagerRegistry $doctrine,
        CacheItemPoolInterface $cache,
        MailerInterface $mailer
    ): Response {
        $form = $this->createForm(ForgotPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $user = $doctrine
                ->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $item  = $cache->getItem('pwd_reset_' . $token);
                $item->set($user->getEmail());
                $item->expiresAfter(self::TTL);
                $cache->save($item);

                $url = $this->generateUrl(
                    'app_reset_password',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $emailMessage = (new TemplatedEmail())
                    ->from('inesjl1961@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->htmlTemplate('emails/reset_password.html.twig')
                    ->context([
                        'resetUrl' => $url,
                        'user'     => $user,
                    ]);

                $mailer->send($emailMessage);
            }

            return $this->redirectToRoute('app_check_email');
        }

        return $this->render('user/forgot_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/auth/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        return $this->render('user/forgot_password/check_email.html.twig');
    }

    #[Route('/auth/reset-password/{token}', name: 'app_reset_password')]
    public function reset(
        string $token,
        Request $request,
        ManagerRegistry $doctrine,
        CacheItemPoolInterface $cache,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em
    ): Response {
        $item = $cache->getItem('pwd_reset_' . $token);
        if (!$item->isHit()) {
            $this->addFlash('error', 'Lien invalide ou expiré.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $email = $item->get();
        $user = $doctrine
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cache->deleteItem('pwd_reset_' . $token);

            $new = $form->get('plainPassword')->getData();
            $user->setPassword($hasher->hashPassword($user, $new));
            $em->flush();

            $this->addFlash('success', 'Mot de passe modifié.');
            return $this->redirectToRoute('app_auth');
        }

        return $this->render('user/forgot_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
