<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Http\Controller;

use App\Identity\Application\Command\RegisterCustomer\RegisterCustomer;
use App\Identity\Application\Command\RegisterCustomer\RegisterCustomerHandler;
use App\Identity\Domain\Model\Customer;
use App\Identity\Presentation\Http\Form\CustomerRegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;

final class CustomerSecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() instanceof Customer) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        RegisterCustomerHandler $registerCustomer,
        UserAuthenticatorInterface $userAuthenticator,
        FormLoginAuthenticator $customerFormLoginAuthenticator,
    ): Response {
        if ($this->getUser() instanceof Customer) {
            return $this->redirectToRoute('app_home');
        }

        $customer = new Customer();
        $form = $this->createForm(CustomerRegistrationFormType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer = $registerCustomer(new RegisterCustomer(
                customer: $customer,
                plainPassword: (string) $form->get('plainPassword')->getData(),
            ));

            $this->addFlash('success', 'Регистрация прошла успешно! Теперь вы можете войти.');

            return $userAuthenticator->authenticateUser($customer, $customerFormLoginAuthenticator, $request);
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
