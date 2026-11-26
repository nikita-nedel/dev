<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateCategory;

use App\Catalog\Domain\Model\Category;

final readonly class CreateCategory
{
    public function __construct(
        public ?Category $parent,
        public string $name,
        public ?string $description = null,
        public ?string $icon = null,
        public int $sortOrder = 0,
    ) {
    }
}
