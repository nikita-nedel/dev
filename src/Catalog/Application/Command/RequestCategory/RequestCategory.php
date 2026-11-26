<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\RequestCategory;

use App\Catalog\Domain\Model\Category;
use App\Identity\Domain\Model\Seller;

final readonly class RequestCategory
{
    public function __construct(
        public Seller $seller,
        public string $name,
        public ?Category $parent = null,
        public ?string $comment = null,
    ) {
    }
}
