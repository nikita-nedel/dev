<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\RequestCategory;

use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Model\CategoryRequest;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryRequestRepositoryInterface;
use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class RequestCategoryHandler
{
    private const int MAX_PENDING_PER_SELLER = 3;

    public function __construct(
        private CategoryRepositoryInterface $categories,
        private CategoryRequestRepositoryInterface $requests,
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(RequestCategory $command): CategoryRequest
    {
        $parent = $command->parent;
        $name = Category::normalizeName($command->name);
        $parentId = $parent?->getId();

        if (null !== $parent && !$parent->isActive()) {
            throw new \RuntimeException('Родительская категория отключена.');
        }

        if (null !== $parent && !$parent->isRoot()) {
            throw new \RuntimeException('Подкатегорию можно создать только внутри корневой категории.');
        }

        if ($this->categories->existsByName($name, $parentId)) {
            throw new \RuntimeException('Такая категория уже существует — выберите её в списке.');
        }

        if ($this->requests->existsPending($command->seller, $name, $parentId)) {
            throw new \RuntimeException('Заявка с таким названием уже на рассмотрении.');
        }

        if ($this->requests->countPendingBySeller($command->seller) >= self::MAX_PENDING_PER_SELLER) {
            throw new \RuntimeException('Нельзя держать больше трёх заявок одновременно.');
        }

        $request = CategoryRequest::submit($command->seller, $parent, $name, $command->comment);

        $this->transactions->transactional(fn () => $this->requests->add($request));

        return $request;
    }
}
