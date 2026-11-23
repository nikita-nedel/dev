<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Application\Command;

use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use Doctrine\ORM\QueryBuilder;

final class InMemoryProductRepository implements ProductRepositoryInterface
{
    /**
     * @var list<Product>
     */
    public array $products = [];

    public function add(Product $product): void
    {
        $this->products[] = $product;
    }

    public function findById(int $id): ?Product
    {
        foreach ($this->products as $product) {
            if ($product->getId() === $id) {
                return $product;
            }
        }

        return null;
    }

    public function createForShopIdsQueryBuilder(array $shopIds): QueryBuilder
    {
        throw new \LogicException('Не используется в юнит-тестах.');
    }

    public function existsByShopAndSku(int $shopId, string $sku, ?int $excludeProductId = null): bool
    {
        foreach ($this->products as $product) {
            if ($product->getShopId() !== $shopId || $product->getSku() !== $sku) {
                continue;
            }

            if (null !== $excludeProductId && $product->getId() === $excludeProductId) {
                continue;
            }

            return true;
        }

        return false;
    }
}
