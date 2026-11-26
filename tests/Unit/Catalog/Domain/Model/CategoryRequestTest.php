<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Domain\Model;

use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Model\CategoryRequest;
use App\Catalog\Domain\Model\CategoryRequestStatus;
use App\Identity\Domain\Model\Admin;
use App\Identity\Domain\Model\Seller;
use App\Shared\Domain\Exception\DomainException;
use PHPUnit\Framework\TestCase;

final class CategoryRequestTest extends TestCase
{
    public function testApproveCreatesLinkToCategory(): void
    {
        $request = CategoryRequest::submit(new Seller(), null, 'Электроника');
        $category = Category::create(null, 'Электроника', 'elektronika');
        $now = new \DateTimeImmutable('2026-11-26 19:00:00');

        $request->approve(new Admin(), $category, $now);

        self::assertSame(CategoryRequestStatus::Approved, $request->getStatus());
        self::assertSame($category, $request->getCreatedCategory());
        self::assertSame($now, $request->getReviewedAt());
        self::assertNull($request->getRejectionReason());
    }

    public function testRejectStoresReason(): void
    {
        $request = CategoryRequest::submit(new Seller(), null, 'Электроника');
        $now = new \DateTimeImmutable('2026-11-26 19:00:00');

        $request->reject(new Admin(), ' Дубликат ', $now);

        self::assertSame(CategoryRequestStatus::Rejected, $request->getStatus());
        self::assertSame('Дубликат', $request->getRejectionReason());
        self::assertSame($now, $request->getReviewedAt());
    }

    public function testRejectRequiresReason(): void
    {
        $request = CategoryRequest::submit(new Seller(), null, 'Электроника');

        $this->expectException(DomainException::class);

        $request->reject(new Admin(), '   ', new \DateTimeImmutable());
    }

    public function testCannotApproveTwice(): void
    {
        $request = CategoryRequest::submit(new Seller(), null, 'Электроника');
        $category = Category::create(null, 'Электроника', 'elektronika');
        $request->approve(new Admin(), $category, new \DateTimeImmutable());

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('уже обработана');

        $request->approve(new Admin(), $category, new \DateTimeImmutable());
    }
}
