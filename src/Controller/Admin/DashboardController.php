<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', 'app_admin_')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', 'dashboard')]
    public function index(): Response
    {
        $stats = [
            'salesCount' => 1847,
            'salesSum' => 12_458_900,
            'visitorsCount' => 24_531,
            'ordersCount' => 892,
            'cartsCount' => 1_204,
            'salesCountTrend' => 12,
            'salesSumTrend' => 8,
        ];
        $recentOrders = [
            [
                'number' => 'WB-10847',
                'customerName' => 'Иван Петров',
                'createdAt' => new \DateTimeImmutable('-15 minutes'),
                'total' => 12490,
                'status' => 'new',
                'statusLabel' => 'Новый',
            ],
            [
                'number' => 'WB-10846',
                'customerName' => 'Мария Сидорова',
                'createdAt' => new \DateTimeImmutable('-1 hour'),
                'total' => 4590,
                'status' => 'processing',
                'statusLabel' => 'В обработке',
            ],
            [
                'number' => 'WB-10845',
                'customerName' => 'Алексей Козлов',
                'createdAt' => new \DateTimeImmutable('-3 hours'),
                'total' => 89990,
                'status' => 'shipped',
                'statusLabel' => 'Отправлен',
            ],
            [
                'number' => 'WB-10844',
                'customerName' => 'Елена Новикова',
                'createdAt' => new \DateTimeImmutable('-5 hours'),
                'total' => 3290,
                'status' => 'delivered',
                'statusLabel' => 'Доставлен',
            ],
            [
                'number' => 'WB-10843',
                'customerName' => 'Дмитрий Волков',
                'createdAt' => new \DateTimeImmutable('-8 hours'),
                'total' => 15600,
                'status' => 'cancelled',
                'statusLabel' => 'Отменён',
            ],
        ];

        return $this->render('admin/index.html.twig', [
            'admin_menu' => 'dashboard',
            'stats' => $stats,
            'recentOrders' => $recentOrders,
        ]);
    }
}
