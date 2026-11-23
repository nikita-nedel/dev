<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\UpdateSellerProfile;

use App\Identity\Domain\Model\Seller;
use App\Seller\Application\Dto\SellerProfileData;

final readonly class UpdateSellerProfile
{
    public function __construct(
        public Seller $seller,
        public SellerProfileData $data,
    ) {
    }
}
