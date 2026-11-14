<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\CurrencyMismatchException;
use App\Shared\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testStoresAmountInMinorUnits(): void
    {
        $money = Money::of(1299, 'BYN');

        self::assertSame(1299, $money->amount());
        self::assertSame('BYN', $money->currency());
    }

    public function testFormatsDecimalString(): void
    {
        self::assertSame('12.99', Money::of(1299, 'BYN')->toDecimalString());
        self::assertSame('12.00', Money::of(1200, 'BYN')->toDecimalString());
        self::assertSame('0.05', Money::of(5, 'BYN')->toDecimalString());
        self::assertSame('-12.99', Money::of(-1299, 'BYN')->toDecimalString());
        self::assertSame('12.99 BYN', Money::of(1299, 'BYN')->format());
    }

    public function testAddAndSubtractReturnNewInstance(): void
    {
        $left = Money::of(1000, 'BYN');
        $right = Money::of(250, 'BYN');

        self::assertTrue($left->add($right)->equals(Money::of(1250, 'BYN')));
        self::assertTrue($left->subtract($right)->equals(Money::of(750, 'BYN')));
        self::assertSame(1000, $left->amount(), 'Исходная сумма не должна меняться');
    }

    public function testMultiply(): void
    {
        self::assertTrue(Money::of(1999, 'BYN')->multiply(3)->equals(Money::of(5997, 'BYN')));
    }

    public function testSignAndComparisonHelpers(): void
    {
        self::assertTrue(Money::zero('BYN')->isZero());
        self::assertTrue(Money::of(-1, 'BYN')->isNegative());
        self::assertFalse(Money::of(1, 'BYN')->isNegative());
        self::assertTrue(Money::of(100, 'BYN')->isGreaterThan(Money::of(99, 'BYN')));
        self::assertFalse(Money::of(99, 'BYN')->isGreaterThan(Money::of(100, 'BYN')));
    }

    public function testCannotOperateOnDifferentCurrencies(): void
    {
        $this->expectException(CurrencyMismatchException::class);

        Money::of(100, 'BYN')->add(Money::of(100, 'USD'));
    }

    public function testRejectsInvalidCurrencyCode(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Money::of(100, 'byn');
    }

    public function testEqualityComparesAmountAndCurrency(): void
    {
        self::assertTrue(Money::of(100, 'BYN')->equals(Money::of(100, 'BYN')));
        self::assertFalse(Money::of(100, 'BYN')->equals(Money::of(100, 'USD')));
        self::assertFalse(Money::of(100, 'BYN')->equals(Money::of(101, 'BYN')));
    }
}
