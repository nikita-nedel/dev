<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Application\Service;

use App\Catalog\Application\Service\GenerateSkuService;
use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\ValueObject\Sku;
use App\Shared\Domain\ValueObject\Money;
use App\Tests\Unit\Catalog\Application\Command\InMemoryProductRepository;
use PHPUnit\Framework\TestCase;

final class GenerateSkuServiceTest extends TestCase
{
    public function testReturnsFirstFreeSku(): void
    {
        $products = new InMemoryProductRepository();
        $products->products[] = Product::create(
            shopId: 3,
            category: Category::create(null, 'Одежда', 'odezhda'),
            name: 'Занятый',
            sku: Sku::fromString('PRD-AAAAAAAA'),
            price: Money::of(1000, 'BYN'),
        );

        $service = new GenerateSkuService(
            new FakeSkuGenerator([
                Sku::fromString('PRD-AAAAAAAA'),
                Sku::fromString('PRD-BBBBBBBB'),
            ]),
            $products,
        );

        self::assertSame('PRD-BBBBBBBB', $service->generate(3)->value());
    }

    public function testFailsWhenAllAttemptsAreTaken(): void
    {
        $products = new InMemoryProductRepository();
        $products->products[] = Product::create(
            shopId: 3,
            category: Category::create(null, 'Одежда', 'odezhda'),
            name: 'Занятый',
            sku: Sku::fromString('PRD-AAAAAAAA'),
            price: Money::of(1000, 'BYN'),
        );

        $taken = [];
        for ($i = 0; $i < 10; ++$i) {
            $taken[] = Sku::fromString('PRD-AAAAAAAA');
        }

        $service = new GenerateSkuService(new FakeSkuGenerator($taken), $products);

        $this->expectException(\RuntimeException::class);

        $service->generate(3);
    }
}
