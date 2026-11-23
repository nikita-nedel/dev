<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\UpdateSellerPersonalData;

use App\Identity\Domain\Model\Seller;
use App\Seller\Application\Dto\SellerPersonalData;

final readonly class UpdateSellerPersonalData
{
    public function __construct(
        public Seller $seller,
        public SellerPersonalData $data,
    ) {
    }
}
