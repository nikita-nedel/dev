<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service;

interface CategorySlugGeneratorInterface
{
    public function generate(string $name): string;
}
