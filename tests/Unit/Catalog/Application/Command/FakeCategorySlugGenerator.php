<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Application\Command;

use App\Catalog\Domain\Service\CategorySlugGeneratorInterface;

final readonly class FakeCategorySlugGenerator implements CategorySlugGeneratorInterface
{
    public function __construct(private string $slug)
    {
    }

    public function generate(string $name): string
    {
        return $this->slug;
    }
}
