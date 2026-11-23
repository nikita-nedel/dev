<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service;

use App\Catalog\Domain\ValueObject\Sku;

interface SkuGeneratorInterface
{
    public function generate(): Sku;
}
