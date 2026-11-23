<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\ArchiveProduct;

use App\Catalog\Domain\Model\Product;

final readonly class ArchiveProduct
{
    public function __construct(
        public Product $product,
    ) {
    }
}
