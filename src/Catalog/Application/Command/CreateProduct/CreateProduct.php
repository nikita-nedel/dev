<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateProduct;

use App\Shared\Domain\ValueObject\Money;

final readonly class CreateProduct
{
    public function __construct(
        public int $shopId,
        public string $name,
        public string $sku,
        public Money $price,
        public ?string $description = null,
    ) {
    }
}
