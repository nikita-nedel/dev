<?php

declare(strict_types=1);

namespace App\Dashboard\Presentation\Http;

use App\Dashboard\Infrastructure\Demo\DemoDashboardData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/dashboard', 'app_admin_dashboard')]
final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly DemoDashboardData $demoData,
    ) {
    }

    #[Route('', name: '', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'admin_menu' => 'dashboard',
            'stats' => $this->demoData->stats(),
            'recentOrders' => $this->demoData->recentOrders(),
        ]);
    }
}
