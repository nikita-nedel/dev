<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Application\Command;

use App\Catalog\Application\Command\RequestCategory\RequestCategory;
use App\Catalog\Application\Command\RequestCategory\RequestCategoryHandler;
use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Model\CategoryRequestStatus;
use App\Identity\Domain\Model\Seller;
use App\Tests\Support\PassthroughTransactionManager;
use PHPUnit\Framework\TestCase;

final class RequestCategoryHandlerTest extends TestCase
{
    private InMemoryCategoryRepository $categories;

    private InMemoryCategoryRequestRepository $requests;

    private Seller $seller;

    protected function setUp(): void
    {
        $this->categories = new InMemoryCategoryRepository();
        $this->requests = new InMemoryCategoryRequestRepository();
        $this->seller = new Seller();
    }

    public function testCreatesPendingRequestForRootCategory(): void
    {
        $handler = $this->handler();

        $request = $handler(new RequestCategory(
            seller: $this->seller,
            name: '  Электроника ',
            comment: ' Нужна для гаджетов ',
        ));

        self::assertSame(CategoryRequestStatus::Pending, $request->getStatus());
        self::assertSame('Электроника', $request->getName());
        self::assertSame('Нужна для гаджетов', $request->getComment());
        self::assertNull($request->getParent());
        self::assertSame([$request], $this->requests->requests);
    }

    public function testCreatesPendingRequestForSubcategory(): void
    {
        $root = Category::create(null, 'Электроника', 'elektronika');
        $this->categories->add($root);

        $request = $this->handler()(new RequestCategory(
            seller: $this->seller,
            name: 'Умный дом',
            parent: $root,
        ));

        self::assertSame($root, $request->getParent());
    }

    public function testRejectsExistingCategory(): void
    {
        $this->categories->add(Category::create(null, 'Одежда', 'odezhda'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Такая категория уже существует');

        $this->handler()(new RequestCategory($this->seller, 'одежда'));
    }

    public function testRejectsDuplicatePendingRequest(): void
    {
        $handler = $this->handler();
        $handler(new RequestCategory($this->seller, 'Электроника'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('уже на рассмотрении');

        $handler(new RequestCategory($this->seller, 'электроника'));
    }

    public function testLimitsPendingRequests(): void
    {
        $handler = $this->handler();

        $handler(new RequestCategory($this->seller, 'Первая'));
        $handler(new RequestCategory($this->seller, 'Вторая'));
        $handler(new RequestCategory($this->seller, 'Третья'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('больше трёх');

        $handler(new RequestCategory($this->seller, 'Четвёртая'));
    }

    public function testRejectsSubcategoryInsideSubcategory(): void
    {
        $root = Category::create(null, 'Электроника', 'elektronika');
        $child = Category::create($root, 'Умный дом', 'umnyj-dom');
        $this->categories->add($root);
        $this->categories->add($child);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('только внутри корневой');

        $this->handler()(new RequestCategory($this->seller, 'Лампочки', $child));
    }

    private function handler(): RequestCategoryHandler
    {
        return new RequestCategoryHandler(
            $this->categories,
            $this->requests,
            new PassthroughTransactionManager(),
        );
    }
}
