<?php

declare(strict_types=1);

namespace App\Cart\Application\Query;

interface CartQueryInterface
{
    /**
     * @return array{cartItems: list<array<string, mixed>>, subtotal: int, discount: int, delivery: int, total: int}
     */
    public function cartPage(): array;
}
