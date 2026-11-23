<?php

declare(strict_types=1);

namespace App\Seller\Domain\Repository;

use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Model\Shop;

interface ShopRepositoryInterface
{
    public function add(Shop $shop): void;

    public function findById(int $id): ?Shop;

    /**
     * @return list<Shop>
     */
    public function findBySeller(Seller $seller): array;
}
