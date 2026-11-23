<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Infrastructure\Service;

use App\Catalog\Infrastructure\Service\RandomSkuGenerator;
use PHPUnit\Framework\TestCase;

final class RandomSkuGeneratorTest extends TestCase
{
    public function testGeneratesSkuInExpectedFormat(): void
    {
        $sku = new RandomSkuGenerator()->generate();

        self::assertMatchesRegularExpression('/^PRD-[A-HJ-NP-Z2-9]{8}$/', $sku->value());
    }
}
