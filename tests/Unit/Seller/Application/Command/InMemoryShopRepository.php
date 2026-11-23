<?php

declare(strict_types=1);

namespace App\Tests\Unit\Seller\Application\Command;

use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Model\Shop;
use App\Seller\Domain\Repository\ShopRepositoryInterface;

final class InMemoryShopRepository implements ShopRepositoryInterface
{
    /**
     * @var list<Shop>
     */
    public array $shops = [];

    public function add(Shop $shop): void
    {
        $this->shops[] = $shop;
    }

    public function findById(int $id): ?Shop
    {
        foreach ($this->shops as $shop) {
            if ($shop->getId() === $id) {
                return $shop;
            }
        }

        return null;
    }

    public function findBySeller(Seller $seller): array
    {
        return array_values(array_filter(
            $this->shops,
            static fn (Shop $shop): bool => $shop->getSeller() === $seller,
        ));
    }
}
