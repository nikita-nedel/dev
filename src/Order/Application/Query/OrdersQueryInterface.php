<?php

declare(strict_types=1);

namespace App\Order\Application\Query;

interface OrdersQueryInterface
{
    /**
     * @return array{orders: list<array<string, mixed>>}
     */
    public function ordersPage(): array;
}
