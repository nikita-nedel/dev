<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\ApproveCategoryRequest;

use App\Catalog\Domain\Model\CategoryRequest;
use App\Identity\Domain\Model\Admin;

final readonly class ApproveCategoryRequest
{
    public function __construct(
        public CategoryRequest $request,
        public Admin $reviewedBy,
    ) {
    }
}
