<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Model\Category;

interface CategoryRepositoryInterface
{
    public function add(Category $category): void;

    public function findById(int $id): ?Category;

    public function existsBySlug(string $slug): bool;

    public function existsByName(string $name, ?int $parentId, ?int $excludeCategoryId = null): bool;

    /**
     * Корни с загруженными подкатегориями (без N+1).
     *
     * @return list<Category>
     */
    public function findRootsWithChildren(bool $onlyActive = false): array;

    public function countProducts(Category $category): int;

    /**
     * @return array<int, int> categoryId => количество товаров
     */
    public function countProductsByCategoryId(): array;
}
