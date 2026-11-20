<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Demo;

use App\Cart\Application\Query\CartQueryInterface;
use App\Catalog\Infrastructure\Demo\DemoCatalogQuery;

final readonly class DemoCartQuery implements CartQueryInterface
{
    public function __construct(private DemoCatalogQuery $catalog)
    {
    }

    public function cartPage(): array
    {
        $cartItems = [
            [
                'product' => $this->catalog->product(1),
                'size' => '42',
                'color' => 'Белый / красный',
                'quantity' => 1,
            ],
            [
                'product' => $this->catalog->product(6),
                'size' => 'One size',
                'color' => 'Чёрный',
                'quantity' => 2,
            ],
            [
                'product' => $this->catalog->product(8),
                'size' => 'Набор',
                'color' => 'Rose',
                'quantity' => 1,
            ],
        ];

        $subtotal = array_reduce($cartItems, static fn (int $sum, array $item): int => $sum + $item['product']['price'] * $item['quantity'], 0);
        $discount = 1850;
        $delivery = 0;

        return [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'delivery' => $delivery,
            'total' => $subtotal - $discount + $delivery,
        ];
    }
}
