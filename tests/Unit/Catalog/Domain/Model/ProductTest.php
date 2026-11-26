<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Domain\Model;

use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Model\ProductStatus;
use App\Catalog\Domain\ValueObject\Sku;
use App\Shared\Domain\Exception\DomainException;
use App\Shared\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    public function testCreateStartsAsDraftAndNormalizesFields(): void
    {
        $product = Product::create(
            shopId: 7,
            category: $this->category(),
            name: '  Футболка  ',
            sku: Sku::fromString('cl-001'),
            price: Money::of(1299, 'BYN'),
            description: '  Хлопок  ',
        );

        self::assertSame(7, $product->getShopId());
        self::assertSame('Футболка', $product->getName());
        self::assertSame('CL-001', $product->getSku());
        self::assertSame('12.99 BYN', $product->getPriceFormatted());
        self::assertSame('Хлопок', $product->getDescription());
        self::assertSame(ProductStatus::Draft, $product->getStatus());
        self::assertFalse($product->isArchived());
    }

    public function testPublishMovesDraftToActive(): void
    {
        $product = $this->product();

        $product->publish();

        self::assertSame(ProductStatus::Active, $product->getStatus());
    }

    public function testPublishTwiceIsRejected(): void
    {
        $product = $this->product();
        $product->publish();

        $this->expectException(DomainException::class);

        $product->publish();
    }

    public function testArchiveMakesProductReadOnly(): void
    {
        $product = $this->product();
        $product->archive();

        self::assertTrue($product->isArchived());

        $this->expectException(DomainException::class);

        $product->rename('Новое имя');
    }

    public function testRejectsZeroPrice(): void
    {
        $this->expectException(DomainException::class);

        Product::create(1, $this->category(), 'Товар', Sku::fromString('SKU-001'), Money::zero('BYN'));
    }

    public function testRejectsForeignCurrency(): void
    {
        $this->expectException(DomainException::class);

        Product::create(1, $this->category(), 'Товар', Sku::fromString('SKU-001'), Money::of(1000, 'USD'));
    }

    public function testChangesPriceAndDescription(): void
    {
        $product = $this->product();

        $product->changePrice(Money::of(2000, 'BYN'));
        $product->changeDescription('  Новое описание  ');

        self::assertSame('20.00 BYN', $product->getPriceFormatted());
        self::assertSame('Новое описание', $product->getDescription());
    }

    public function testRejectsInactiveCategory(): void
    {
        $category = $this->category();
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

    private function category(): Category
    {
        return Category::create(null, 'Одежда', 'odezhda');
    }

    private function product(): Product
    {
        return Product::create(
            shopId: 1,
            category: $this->category(),
            name: 'Товар',
            sku: Sku::fromString('SKU-001'),
            price: Money::of(1000, 'BYN'),
        );
    }
}
