<?php
// src/Controller/SecurityController.php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/auth/modal/{type}', name: 'app_auth_modal', methods: ['GET'])]
    public function authModal(string $type, AuthenticationUtils $authenticationUtils): Response
    {
        if ($type === 'login') {
            return $this->render('components/security/login_form.html.twig', [
                'error' => $authenticationUtils->getLastAuthenticationError(),
                'last_username' => $authenticationUtils->getLastUsername(),
            ]);
        }

        if ($type === 'register') {
            $form = $this->createForm(RegistrationFormType::class);
            return $this->render('components/security/register_form.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
        }

        throw $this->createNotFoundException('Form type not found');
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
//        if ($request->isXmlHttpRequest()) {
            $error = $authenticationUtils->getLastAuthenticationError();
            $lastUsername = $authenticationUtils->getLastUsername();

//            if ($error) {
                return new JsonResponse([
                    'html' => $this->renderView('components/security/login_form.html.twig', [
                        'error' => $error,
                        'last_username' => $lastUsername
                    ])
                ]);
//            }

//            return new JsonResponse(['success' => true]);
//        }

//        return $this->redirectToRoute('app_home');
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setRoles(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Регистрация прошла успешно! Теперь вы можете войти.');
            return $this->redirectToRoute('app_home');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => false,
                'html' => $this->renderView('components/security/register_form.html.twig', [
                    'registrationForm' => $form->createView(),
                ])
            ]);
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route(path: '/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}