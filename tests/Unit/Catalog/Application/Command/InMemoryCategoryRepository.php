<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Application\Command;

use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;

final class InMemoryCategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var list<Category>
     */
    public array $categories = [];

    /**
     * @var array<int, int>
     */
    public array $productCounts = [];

    public function add(Category $category): void
    {
        if (!in_array($category, $this->categories, true)) {
            $this->categories[] = $category;
        }
    }

    public function findById(int $id): ?Category
    {
        foreach ($this->categories as $category) {
            if ($category->getId() === $id) {
                return $category;
            }
        }

        return null;
    }

    public function existsBySlug(string $slug): bool
    {
        return array_any($this->categories, fn ($category) => $category->getSlug() === $slug);
    }

    public function existsByName(string $name, ?int $parentId, ?int $excludeCategoryId = null): bool
    {
        foreach ($this->categories as $category) {
            if (null !== $excludeCategoryId && $category->getId() === $excludeCategoryId) {
                continue;
            }

            if (mb_strtolower($category->getName()) !== mb_strtolower($name)) {
                continue;
            }

            if (null === $parentId ? $category->isRoot() : $category->getParent()?->getId() === $parentId) {
                return true;
            }
        }

        return false;
    }

    public function findRootsWithChildren(bool $onlyActive = false): array
    {
        return array_values(array_filter(
            $this->categories,
            static fn (Category $category): bool => $category->isRoot() && (!$onlyActive || $category->isActive()),
        ));
    }

    public function countProducts(Category $category): int
    {
        return $this->productCounts[$this->key($category)] ?? 0;
    }

    public function countProductsByCategoryId(): array
    {
        $counts = [];

        foreach ($this->productCounts as $key => $count) {
            foreach ($this->categories as $category) {
                if (null !== $category->getId() && $category->getId() === $key) {
                    $counts[$key] = $count;
                }
            }
        }

        return $counts;
    }

    private function key(Category $category): int
    {
        return $category->getId() ?? spl_object_id($category);
    }
}
