<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Demo;

use App\Catalog\Infrastructure\Demo\DemoCatalogQuery;
use App\Order\Application\Query\OrdersQueryInterface;

final readonly class DemoOrdersQuery implements OrdersQueryInterface
{
    public function __construct(private DemoCatalogQuery $catalog)
    {
    }

    public function ordersPage(): array
    {
        $orders = [
            [
                'number' => 'WB-20481',
                'date' => new \DateTimeImmutable('-1 day'),
                'status' => 'В пути',
                'statusType' => 'shipping',
                'total' => 12980,
                'address' => 'ПВЗ WB Style, Москва, ул. Лесная, 7',
                'products' => [
                    $this->catalog->product(1),
                    $this->catalog->product(6),
                ],
                'steps' => ['Оформлен', 'Оплачен', 'Собран', 'В пути'],
                'activeStep' => 3,
            ],
            [
                'number' => 'WB-20396',
                'date' => new \DateTimeImmutable('-8 days'),
                'status' => 'Получен',
                'statusType' => 'done',
                'total' => 7980,
                'address' => 'Курьером до двери',
                'products' => [
                    $this->catalog->product(4),
                    $this->catalog->product(8),
                ],
                'steps' => ['Оформлен', 'Оплачен', 'Доставлен', 'Получен'],
                'activeStep' => 4,
            ],
            [
                'number' => 'WB-20142',
                'date' => new \DateTimeImmutable('-21 days'),
                'status' => 'Получен',
                'statusType' => 'done',
                'total' => 29990,
                'address' => 'ПВЗ WB Style, Санкт-Петербург, Невский пр., 45',
                'products' => [
                    $this->catalog->product(5),
                ],
                'steps' => ['Оформлен', 'Оплачен', 'Доставлен', 'Получен'],
                'activeStep' => 4,
            ],
        ];

        return ['orders' => $orders];
    }
}
