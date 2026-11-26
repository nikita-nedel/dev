<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\RejectCategoryRequest;

use App\Catalog\Domain\Model\CategoryRequest;
use App\Identity\Domain\Model\Admin;

final readonly class RejectCategoryRequest
{
    public function __construct(
        public CategoryRequest $request,
        public Admin $reviewedBy,
        public string $reason,
    ) {
    }
}
