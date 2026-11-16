<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Http\Controller;

use App\Identity\Application\Command\CreateAdminInvite\CreateAdminInvite;
use App\Identity\Application\Command\CreateAdminInvite\CreateAdminInviteHandler;
use App\Identity\Application\Command\RegisterAdminFromInvite\RegisterAdminFromInvite;
use App\Identity\Application\Command\RegisterAdminFromInvite\RegisterAdminFromInviteHandler;
use App\Identity\Domain\Model\Admin;
use App\Identity\Domain\Repository\AdminInviteRepositoryInterface;
use App\Identity\Presentation\Http\Form\AdminInviteCreateFormType;
use App\Identity\Presentation\Http\Form\AdminInviteRegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;

final class AdminInviteController extends AbstractController
{
    #[Route('/admin/invite/{token}', name: 'admin_invite_register', methods: ['GET', 'POST'])]
    public function registerFromInvite(
        string $token,
        Request $request,
        AdminInviteRepositoryInterface $invites,
        RegisterAdminFromInviteHandler $registerAdmin,
        UserAuthenticatorInterface $userAuthenticator,
        FormLoginAuthenticator $adminFormLoginAuthenticator,
    ): Response {
        $invite = $invites->findValidByToken($token);

        if (null === $invite) {
            $this->addFlash('error', 'Ссылка-приглашение недействительна или истекла.');

            return $this->redirectToRoute('admin_login');
        }

        $form = $this->createForm(AdminInviteRegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $admin = $registerAdmin(new RegisterAdminFromInvite(
                invite: $invite,
                firstName: (string) $form->get('firstName')->getData(),
                lastName: (string) $form->get('lastName')->getData(),
                plainPassword: (string) $form->get('plainPassword')->getData(),
            ));

            $this->addFlash('success', 'Аккаунт администратора создан.');

            return $userAuthenticator->authenticateUser($admin, $adminFormLoginAuthenticator, $request);
        }

        return $this->render('admin/invite/register.html.twig', [
            'form' => $form->createView(),
            'inviteEmail' => $invite->getEmail(),
        ]);
    }

    #[Route('/admin/admins/invite', name: 'admin_invite_create', methods: ['GET', 'POST'])]
    public function createInvite(Request $request, CreateAdminInviteHandler $createInvite): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(AdminInviteCreateFormType::class);
        $form->handleRequest($request);

        $inviteUrl = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $createdBy = $this->getUser();

            $invite = $createInvite(new CreateAdminInvite(
                email: (string) $form->get('email')->getData(),
                createdBy: $createdBy instanceof Admin ? $createdBy : null,
            ));

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
