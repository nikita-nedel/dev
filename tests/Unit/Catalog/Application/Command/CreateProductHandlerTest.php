<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Application\Command;

use App\Catalog\Application\Command\CreateProduct\CreateProduct;
use App\Catalog\Application\Command\CreateProduct\CreateProductHandler;
use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Model\ProductStatus;
use App\Shared\Domain\ValueObject\Money;
use App\Tests\Support\PassthroughTransactionManager;
use PHPUnit\Framework\TestCase;

final class CreateProductHandlerTest extends TestCase
{
    public function testCreatesDraftProductWithNormalizedSku(): void
    {
        $products = new InMemoryProductRepository();
        $handler = new CreateProductHandler($products, new PassthroughTransactionManager());

        $product = $handler(new CreateProduct(
            shopId: 3,
            category: $this->category(),
            name: '  Футболка ',
            sku: ' cl-001 ',
            price: Money::of(1299, 'BYN'),
            description: ' Описание ',
        ));

        self::assertSame('Футболка', $product->getName());
        self::assertSame('CL-001', $product->getSku());
        self::assertSame(ProductStatus::Draft, $product->getStatus());
        self::assertSame([$product], $products->products);
    }

    public function testRejectsDuplicateSkuInSameShop(): void
    {
        $products = new InMemoryProductRepository();
        $handler = new CreateProductHandler($products, new PassthroughTransactionManager());

        $handler(new CreateProduct(3, $this->category(), 'Первый', 'CL-001', Money::of(1000, 'BYN')));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Товар с таким артикулом уже есть в магазине.');

        $handler(new CreateProduct(3, $this->category(), 'Второй', 'cl-001', Money::of(2000, 'BYN')));
    }

    private function category(): Category
    {
        return Category::create(null, 'Одежда', 'odezhda');
    }
}
