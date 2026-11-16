<?php

declare(strict_types=1);

namespace App\Catalog\Application\Query;

interface CatalogQueryInterface
{
    /**
     * @return array{slides: list<array<string, mixed>>, categories: list<array<string, mixed>>, products: list<array<string, mixed>>}
     */
    public function homePage(): array;

    /**
     * @return array{category: array<string, mixed>, categories: array<string, array<string, mixed>>, brands: list<array<string, mixed>>, sizes: list<string>, colors: list<array<string, mixed>>, products: list<array<string, mixed>>}
     */
    public function catalogPage(string $slug): array;

    /**
     * @return array{product: array<string, mixed>, relatedProducts: list<array<string, mixed>>}
     */
    public function productPage(int $id): array;
}
