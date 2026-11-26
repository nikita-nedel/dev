<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Service;

use App\Catalog\Domain\Service\CategorySlugGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

final readonly class AsciiCategorySlugGenerator implements CategorySlugGeneratorInterface
{
    public function generate(string $name): string
    {
        $slug = trim(new AsciiSlugger('ru')->slug($name)->lower()->toString(), '-');

        return '' !== $slug ? $slug : 'category-' . bin2hex(random_bytes(3));
    }
}
