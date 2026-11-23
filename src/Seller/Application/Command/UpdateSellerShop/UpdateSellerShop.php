<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\UpdateSellerShop;

use App\Identity\Domain\Model\Seller;
use App\Seller\Application\Dto\SellerShopData;
use App\Seller\Domain\Model\Shop;

final readonly class UpdateSellerShop
{
    public function __construct(
        public Seller $seller,
        public SellerShopData $data,
        public ?Shop $shop = null,
    ) {
    }
}
