<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Form\AdminInviteCreateFormType;
use App\Form\AdminInviteRegistrationFormType;
use App\Service\AdminInviteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;

#[Route('/admin')]
class AdminInviteController extends AbstractController
{
    public function __construct(
        private readonly AdminInviteService $adminInviteService,
    ) {
    }

    #[Route('/invite/{token}', name: 'admin_invite_register', methods: ['GET', 'POST'])]
    public function registerFromInvite(
        string $token,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        FormLoginAuthenticator $adminFormLoginAuthenticator,
    ): Response {
        $invite = $this->adminInviteService->findValidInvite($token);
        if (null === $invite) {
            $this->addFlash('error', 'Ссылка-приглашение недействительна или истекла.');

            return $this->redirectToRoute('admin_login');
        }

        $form = $this->createForm(AdminInviteRegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $admin = $this->adminInviteService->registerAdminFromInvite(
                $invite,
                (string) $form->get('firstName')->getData(),
                (string) $form->get('lastName')->getData(),
                $passwordHasher->hashPassword(new Admin(), (string) $form->get('plainPassword')->getData()),
            );

            $this->addFlash('success', 'Аккаунт администратора создан.');

            return $userAuthenticator->authenticateUser($admin, $adminFormLoginAuthenticator, $request);
        }

        return $this->render('admin/invite/register.html.twig', [
            'form' => $form->createView(),
            'inviteEmail' => $invite->getEmail(),
        ]);
    }

    #[Route('/admins/invite', name: 'admin_invite_create', methods: ['GET', 'POST'])]
    public function createInvite(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(AdminInviteCreateFormType::class);
        $form->handleRequest($request);

        $inviteUrl = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $createdBy = $this->getUser();
            $invite = $this->adminInviteService->createInvite(
                (string) $form->get('email')->getData(),
                $createdBy instanceof Admin ? $createdBy : null,
            );

            $inviteUrl = $this->generateUrl(
                'admin_invite_register',
                ['token' => $invite->getToken()],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );

            $this->addFlash('success', 'Приглашение создано.');
        }

        return $this->render('admin/invite/create.html.twig', [
            'admin_menu' => 'admins',
            'form' => $form->createView(),
            'inviteUrl' => $inviteUrl,
        ]);
    }
}
