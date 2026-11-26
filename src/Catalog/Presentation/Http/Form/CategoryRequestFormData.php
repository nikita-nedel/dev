<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\Form;

use App\Catalog\Domain\Model\Category;

final class CategoryRequestFormData
{
    public ?Category $parent = null;

    public string $name = '';

    public ?string $comment = null;
}
