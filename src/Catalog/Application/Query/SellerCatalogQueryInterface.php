<?php

declare(strict_types=1);

namespace App\Catalog\Application\Query;

interface SellerCatalogQueryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function products(): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function categories(): array;

    /**
     * @return array{
     *     total: int,
     *     active: int,
     *     inactive: int,
     *     outOfStock: int,
     *     lowStock: int,
     *     stockTotal: int,
     *     categories: int
     * }
     */
    public function stats(): array;
}
