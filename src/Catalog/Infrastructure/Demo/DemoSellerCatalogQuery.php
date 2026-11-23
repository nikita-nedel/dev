<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Demo;

use App\Catalog\Application\Query\SellerCatalogQueryInterface;

final readonly class DemoSellerCatalogQuery implements SellerCatalogQueryInterface
{
    public function products(): array
    {
        $categories = $this->categories();

        return [
            [
                'id' => 1,
                'name' => 'Смартфон X',
                'sku' => 'PH-001',
                'description' => 'Флагманский смартфон с ярким дисплеем и камерой для повседневных задач.',
                'category' => $categories[0],
                'price' => 29990,
                'stock' => 45,
                'active' => true,
                'image' => null,
            ],
            [
                'id' => 2,
                'name' => 'Беспроводные наушники',
                'sku' => 'AU-202',
                'description' => 'Беспроводные наушники с активным шумоподавлением и запасом автономности.',
                'category' => $categories[0],
                'price' => 4990,
                'stock' => 120,
                'active' => true,
                'image' => null,
            ],
            [
                'id' => 3,
                'name' => 'Футболка хлопок',
                'sku' => 'CL-012',
                'description' => 'Базовая футболка из плотного хлопка свободного кроя.',
                'category' => $categories[1],
                'price' => 1290,
                'stock' => 0,
                'active' => false,
                'image' => null,
            ],
            [
                'id' => 4,
                'name' => 'Набор садовых инструментов',
                'sku' => 'GR-007',
                'description' => 'Практичный набор инструментов для дачи, сада и мелкого ремонта.',
                'category' => $categories[2],
                'price' => 3490,
                'stock' => 8,
                'active' => true,
                'image' => null,
            ],
        ];
    }

    public function categories(): array
    {
        return [
            ['id' => 1, 'name' => 'Электроника'],
            ['id' => 2, 'name' => 'Одежда'],
            ['id' => 3, 'name' => 'Дом и сад'],
        ];
    }

    public function stats(): array
    {
        $products = $this->products();

        $active = count(array_filter($products, static fn (array $product): bool => true === $product['active']));
        $outOfStock = count(array_filter($products, static fn (array $product): bool => 0 === $product['stock']));
        $lowStock = count(array_filter(
            $products,
            static fn (array $product): bool => 0 < $product['stock'] && $product['stock'] <= 10,
        ));
        $categoryIds = array_unique(array_map(
            static fn (array $product): int => $product['category']['id'],
            $products,
        ));

        return [
            'total' => count($products),
            'active' => $active,
            'inactive' => count($products) - $active,
            'outOfStock' => $outOfStock,
            'lowStock' => $lowStock,
            'stockTotal' => array_sum(array_column($products, 'stock')),
            'categories' => count($categoryIds),
        ];
    }
}
