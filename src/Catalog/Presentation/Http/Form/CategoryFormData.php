<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\Form;

use App\Catalog\Domain\Model\Category;

final class CategoryFormData
{
    public ?Category $parent = null;

    public string $name = '';

    public ?string $description = null;

    public ?string $icon = null;

    public int $sortOrder = 0;

    public static function fromCategory(Category $category): self
    {
        $data = new self();
        $data->parent = $category->getParent();
        $data->name = $category->getName();
        $data->description = $category->getDescription();
        $data->icon = $category->getIcon();
        $data->sortOrder = $category->getSortOrder();

        return $data;
    }
}
