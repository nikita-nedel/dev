<?php

declare(strict_types=1);

namespace App\Identity\Presentation\Http\Controller;

use App\Identity\Infrastructure\Demo\DemoProfileData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile', name: 'app_profile')]
#[IsGranted('ROLE_CUSTOMER')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly DemoProfileData $demoData,
    ) {
    }

    public function __invoke(): Response
    {
        return $this->render('profile/index.html.twig', $this->demoData->profilePage());
    }
}
