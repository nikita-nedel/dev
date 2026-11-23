<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\PublishProduct;

use App\Catalog\Domain\Model\Product;

final readonly class PublishProduct
{
    public function __construct(
        public Product $product,
    ) {
    }
}
