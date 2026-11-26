<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateCategory;

use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class UpdateCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(UpdateCategory $command): Category
    {
        $category = $command->category;
        $name = Category::normalizeName($command->name);

        if ($this->categories->existsByName($name, $category->getParent()?->getId(), $category->getId())) {
            throw new \RuntimeException('Категория с таким названием уже есть на этом уровне.');
        }

        $this->transactions->transactional(function () use ($category, $command, $name): void {
            $category->rename($name);
            $category->updateMeta($command->description, $command->icon, $command->sortOrder);
        });

        return $category;
    }
}
