<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Model\Product;
use Doctrine\ORM\QueryBuilder;

interface ProductRepositoryInterface
{
    public function add(Product $product): void;

    public function findById(int $id): ?Product;

    /**
     * @param list<int> $shopIds
     */
    public function createForShopIdsQueryBuilder(array $shopIds): QueryBuilder;

    public function existsByShopAndSku(int $shopId, string $sku, ?int $excludeProductId = null): bool;
}
