<?php

declare(strict_types=1);

namespace App\Seller\Presentation\Http\Controller;

use App\Identity\Domain\Model\Seller;
use App\Identity\Domain\Repository\SellerRepositoryInterface;
use App\Seller\Application\Command\ActivateSeller\ActivateSeller;
use App\Seller\Application\Command\ActivateSeller\ActivateSellerHandler;
use App\Seller\Presentation\Http\Form\SellerActivationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;

final class SellerSecurityController extends AbstractController
{
    #[Route('/seller/login', name: 'seller_login')]
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

    #[Route('/seller/logout', name: 'seller_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/seller/activate/{token}', name: 'seller_activate', methods: ['GET', 'POST'])]
    public function activate(
        string $token,
        Request $request,
        SellerRepositoryInterface $sellers,
        ActivateSellerHandler $activateSeller,
        UserAuthenticatorInterface $userAuthenticator,
        FormLoginAuthenticator $sellerFormLoginAuthenticator,
    ): Response {
        $seller = $sellers->findByActivationToken($token);

        if (null === $seller) {
            $this->addFlash('error', 'Ссылка активации недействительна или истекла.');

            return $this->redirectToRoute('seller_login');
        }

        $form = $this->createForm(SellerActivationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activateSeller(new ActivateSeller(
                seller: $seller,
                plainPassword: (string) $form->get('plainPassword')->getData(),
            ));

            $this->addFlash('success', 'Аккаунт продавца активирован.');

            return $userAuthenticator->authenticateUser($seller, $sellerFormLoginAuthenticator, $request);
        }

        return $this->render('seller/security/activate.html.twig', [
            'form' => $form->createView(),
            'sellerEmail' => $seller->getEmail(),
        ]);
    }
}
