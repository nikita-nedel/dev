<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Domain\ValueObject;

use App\Catalog\Domain\ValueObject\Sku;
use App\Shared\Domain\Exception\DomainException;
use PHPUnit\Framework\TestCase;

final class SkuTest extends TestCase
{
    public function testNormalizesCaseAndWhitespace(): void
    {
        self::assertSame('CL-001', Sku::fromString('  cl-001  ')->value());
    }

    public function testRejectsTooShortValue(): void
    {
        $this->expectException(DomainException::class);

        Sku::fromString('ab');
    }

    public function testRejectsInvalidCharacters(): void
    {
        $this->expectException(DomainException::class);

        Sku::fromString('CL 001');
    }

    public function testRejectsTooLongValue(): void
    {
        $this->expectException(DomainException::class);

        Sku::fromString(str_repeat('A', Sku::MAX_LENGTH + 1));
    }
}
