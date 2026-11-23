<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateProduct;

use App\Catalog\Domain\Model\Product;
use App\Shared\Domain\ValueObject\Money;

final readonly class UpdateProduct
{
    public function __construct(
        public Product $product,
        public string $name,
        public string $sku,
        public Money $price,
        public ?string $description = null,
    ) {
    }
}
