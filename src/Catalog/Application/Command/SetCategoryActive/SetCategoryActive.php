<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\SetCategoryActive;

use App\Catalog\Domain\Model\Category;

final readonly class SetCategoryActive
{
    public function __construct(
        public Category $category,
        public bool $active,
    ) {
    }
}
