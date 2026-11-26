<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateCategory;

use App\Catalog\Application\Service\CategoryCreator;
use App\Catalog\Domain\Model\Category;
use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class CreateCategoryHandler
{
    public function __construct(
        private CategoryCreator $categoryCreator,
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(CreateCategory $command): Category
    {
        return $this->transactions->transactional(fn (): Category => $this->categoryCreator->create(
            parent: $command->parent,
            name: $command->name,
            description: $command->description,
            icon: $command->icon,
            sortOrder: $command->sortOrder,
        ));
    }
}
