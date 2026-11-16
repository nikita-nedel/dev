<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Demo;

final class DemoDashboardData
{
    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        return [
            'salesCount' => 1847,
            'salesSum' => 12_458_900,
            'visitorsCount' => 24_531,
            'ordersCount' => 892,
            'cartsCount' => 1_204,
            'salesCountTrend' => 12,
            'salesSumTrend' => 8,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentOrders(): array
    {
        return [
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
    }
}
