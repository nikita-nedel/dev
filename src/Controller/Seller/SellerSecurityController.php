<?php

declare(strict_types=1);

namespace App\Controller\Seller;

use App\Entity\Seller;
use App\Form\SellerActivationFormType;
use App\Repository\SellerRepository;
use App\Service\SellerRequestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;

#[Route('/seller')]
class SellerSecurityController extends AbstractController
{
    #[Route('/login', name: 'seller_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() instanceof Seller) {
            return $this->redirectToRoute('app_seller_dashboard');
        }

        return $this->render('seller/security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/logout', name: 'seller_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/activate/{token}', name: 'seller_activate', methods: ['GET', 'POST'])]
    public function activate(
        string $token,
        Request $request,
        SellerRepository $sellerRepository,
        SellerRequestService $sellerRequestService,
        UserPasswordHasherInterface $passwordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        FormLoginAuthenticator $sellerFormLoginAuthenticator,
    ): Response {
        $seller = $sellerRepository->findByActivationToken($token);
        if (null === $seller) {
            $this->addFlash('error', 'Ссылка активации недействительна или истекла.');

            return $this->redirectToRoute('seller_login');
        }

        $form = $this->createForm(SellerActivationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sellerRequestService->activateSeller(
                $seller,
                $passwordHasher->hashPassword($seller, (string) $form->get('plainPassword')->getData()),
            );

            $this->addFlash('success', 'Аккаунт продавца активирован.');

            return $userAuthenticator->authenticateUser($seller, $sellerFormLoginAuthenticator, $request);
        }

        return $this->render('seller/security/activate.html.twig', [
            'form' => $form->createView(),
            'sellerEmail' => $seller->getEmail(),
        ]);
    }
}
