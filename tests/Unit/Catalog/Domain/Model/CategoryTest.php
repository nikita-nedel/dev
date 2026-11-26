<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Domain\Model;

use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\ValueObject\Sku;
use App\Shared\Domain\Exception\DomainException;
use App\Shared\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class CategoryTest extends TestCase
{
    public function testCreatesRootAndSubcategory(): void
    {
        $root = Category::create(null, 'Электроника', 'elektronika');
        $child = Category::create($root, 'Умный дом', 'umnyj-dom');

        self::assertTrue($root->isRoot());
        self::assertFalse($child->isRoot());
        self::assertSame($root, $child->getParent());
        self::assertTrue($child->isActive());
    }

    public function testRejectsThirdLevel(): void
    {
        $root = Category::create(null, 'Электроника', 'elektronika');
        $child = Category::create($root, 'Умный дом', 'umnyj-dom');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Поддерживается максимум два уровня');

        Category::create($child, 'Лампочки', 'lampochki');
    }

    public function testNormalizesName(): void
    {
        $category = Category::create(null, '  Умный   дом  ', 'umnyj-dom');

        self::assertSame('Умный дом', $category->getName());
    }

    public function testRejectsEmptyName(): void
    {
        $this->expectException(DomainException::class);

        Category::create(null, '   ', 'empty');
    }

    public function testDeactivateTwiceIsRejected(): void
    {
        $category = Category::create(null, 'Одежда', 'odezhda');
        $category->deactivate();

        self::assertFalse($category->isActive());

        $this->expectException(DomainException::class);

        $category->deactivate();
    }

    public function testRenameKeepsSlug(): void
    {
        $category = Category::create(null, 'Одежда', 'odezhda');

        $category->rename('  Одежда и обувь ');

        self::assertSame('Одежда и обувь', $category->getName());
        self::assertSame('odezhda', $category->getSlug());
    }

    public function testProductCannotBeAttachedToInactiveCategory(): void
    {
        $category = Category::create(null, 'Одежда', 'odezhda');
        $category->deactivate();

        $this->expectException(DomainException::class);

        Product::create(
            shopId: 1,
            category: $category,
            name: 'Товар',
            sku: Sku::fromString('SKU-001'),
            price: Money::of(1000, 'BYN'),
        );
    }
}
