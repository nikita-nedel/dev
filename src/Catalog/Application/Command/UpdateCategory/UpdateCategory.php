<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateCategory;

use App\Catalog\Domain\Model\Category;

final readonly class UpdateCategory
{
    public function __construct(
        public Category $category,
        public string $name,
        public ?string $description = null,
        public ?string $icon = null,
        public int $sortOrder = 0,
    ) {
    }
}
