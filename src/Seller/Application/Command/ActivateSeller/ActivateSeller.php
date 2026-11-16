<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\ActivateSeller;

use App\Identity\Domain\Model\Seller;

final readonly class ActivateSeller
{
    public function __construct(
        public Seller $seller,
        public string $plainPassword,
    ) {
    }
}
