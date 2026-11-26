<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service;

use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;

final readonly class CategoryCreator
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private CategorySlugService $slugs,
    ) {
    }

    public function create(
        ?Category $parent,
        string $name,
        ?string $description = null,
        ?string $icon = null,
        int $sortOrder = 0,
    ): Category {
        $name = Category::normalizeName($name);

        if (null !== $parent && !$parent->isActive()) {
            throw new \RuntimeException('Родительская категория отключена.');
        }

        if (null !== $parent && $this->categories->countProducts($parent) > 0) {
            throw new \RuntimeException('В категории уже есть товары. Сначала перенесите их в другую категорию.');
        }

        if ($this->categories->existsByName($name, $parent?->getId())) {
            throw new \RuntimeException('Категория с таким названием уже есть на этом уровне.');
        }

        $category = Category::create(
            parent: $parent,
            name: $name,
            slug: $this->slugs->uniqueFor($name),
            description: $description,
            icon: $icon,
            sortOrder: $sortOrder,
        );

        $this->categories->add($category);

        return $category;
    }
}
