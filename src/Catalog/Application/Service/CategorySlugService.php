<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service;

use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Catalog\Domain\Service\CategorySlugGeneratorInterface;

final readonly class CategorySlugService
{
    public function __construct(
        private CategorySlugGeneratorInterface $generator,
        private CategoryRepositoryInterface $categories,
    ) {
    }

    public function uniqueFor(string $name): string
    {
        $base = $this->generator->generate($name);
        $slug = $base;

        for ($i = 2; $this->categories->existsBySlug($slug); ++$i) {
            $slug = sprintf('%s-%d', $base, $i);
        }

        return $slug;
    }
}
