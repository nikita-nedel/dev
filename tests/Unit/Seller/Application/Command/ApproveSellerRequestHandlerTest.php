<?php

declare(strict_types=1);

namespace App\Tests\Unit\Seller\Application\Command;

use App\Identity\Domain\Model\Admin;
use App\Identity\Domain\Model\Customer;
use App\Identity\Domain\Model\Seller;
use App\Seller\Application\Command\ApproveSellerRequest\ApproveSellerRequest;
use App\Seller\Application\Command\ApproveSellerRequest\ApproveSellerRequestHandler;
use App\Seller\Domain\Model\SellerRequest;
use App\Seller\Domain\Model\SellerRequestStatus;
use App\Tests\Support\FixedClock;
use App\Tests\Support\PassthroughTransactionManager;
use PHPUnit\Framework\TestCase;

final class ApproveSellerRequestHandlerTest extends TestCase
{
    public function testApprovalIssuesActivationTokenForSevenDays(): void
    {
        $clock = new FixedClock(new \DateTimeImmutable('2026-02-01 09:30:00'));
        $shops = new InMemoryShopRepository();
        $handler = new ApproveSellerRequestHandler($clock, $shops, new PassthroughTransactionManager());

        [$request, $seller] = $this->pendingRequest();
        $admin = new Admin()->setEmail('admin@example.com');

        $approvedSeller = $handler(new ApproveSellerRequest($request, $admin));

        self::assertCount(1, $shops->shops);
        self::assertSame('Магазин', $shops->shops[0]->getName());
        self::assertSame($seller, $shops->shops[0]->getSeller());

        self::assertSame($seller, $approvedSeller);
        self::assertSame(SellerRequestStatus::Approved, $request->getStatus());
        self::assertSame($admin, $request->getReviewedBy());
        self::assertSame('2026-02-01 09:30:00', $request->getReviewedAt()?->format('Y-m-d H:i:s'));

        self::assertNotNull($seller->getActivationToken());
        self::assertSame(64, \strlen((string) $seller->getActivationToken()));
        self::assertSame('2026-02-08 09:30:00', $seller->getActivationTokenExpiresAt()?->format('Y-m-d H:i:s'));
        self::assertSame('2026-02-01 09:30:00', $seller->getApprovedAt()?->format('Y-m-d H:i:s'));
    }

    public function testAlreadyProcessedRequestCannotBeApprovedTwice(): void
    {
        $shops = new InMemoryShopRepository();

        $handler = new ApproveSellerRequestHandler(
            new FixedClock(new \DateTimeImmutable('2026-02-01 09:30:00')),
            $shops,
            new PassthroughTransactionManager(),
        );

        [$request] = $this->pendingRequest();
        $request->setStatus(SellerRequestStatus::Approved);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Заявка уже обработана.');

        $handler(new ApproveSellerRequest($request, new Admin()->setEmail('admin@example.com')));
    }

    public function testRequestWithoutSellerCannotBeApproved(): void
    {
        $shops = new InMemoryShopRepository();

        $handler = new ApproveSellerRequestHandler(
            new FixedClock(new \DateTimeImmutable('2026-02-01 09:30:00')),
            $shops,
            new PassthroughTransactionManager(),
        );

        $request = new SellerRequest()->setShopName('Магазин');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Продавец не найден для заявки.');

        $handler(new ApproveSellerRequest($request, new Admin()->setEmail('admin@example.com')));
    }

    /**
     * @return array{SellerRequest, Seller}
     */
    private function pendingRequest(): array
    {
        $customer = new Customer()->setEmail('seller@example.com');

        $seller = new Seller()
            ->setEmail('seller@example.com')
            ->setCustomer($customer)
            ->setRoles(['ROLE_SELLER'])
            ->setIsActive(false);

        $request = new SellerRequest()
            ->setSeller($seller)
            ->setShopName('Магазин')
            ->setStatus(SellerRequestStatus::Pending);

        return [$request, $seller];
    }
}
