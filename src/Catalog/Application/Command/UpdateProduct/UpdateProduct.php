<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateProduct;

use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Model\Product;
use App\Shared\Domain\ValueObject\Money;

final readonly class UpdateProduct
{
    public function __construct(
        public Product $product,
        public Category $category,
        public string $name,
        public string $sku,
        public Money $price,
        public ?string $description = null,
    ) {
    }
}
