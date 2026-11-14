<?php

declare(strict_types=1);

namespace App\Tests\Functional\Seller;

use App\Entity\Admin;
use App\Entity\SellerRequest;
use App\Enum\SellerRequestStatus;
use App\Tests\Functional\FunctionalTestCase;

final class SellerRequestFlowTest extends FunctionalTestCase
{
    public function testCustomerCanApplyForSeller(): void
    {
        $customer = $this->createCustomer('future-seller@example.com');
        $this->client->loginUser($customer, 'customer_context');

        $this->client->request('POST', '/become-seller', [
            'seller_request_form' => $this->applicationData(),
        ]);

        $this->assertRedirectedTo('/become-seller');

        $sellerRequest = $this->em()->getRepository(SellerRequest::class)->findOneBy([]);
        self::assertInstanceOf(SellerRequest::class, $sellerRequest);
        self::assertSame(SellerRequestStatus::Pending, $sellerRequest->getStatus());
        self::assertSame('Магазин Ивана', $sellerRequest->getShopName());
        self::assertNotNull($sellerRequest->getSeller());
    }

    public function testSecondApplicationIsNotCreated(): void
    {
        $customer = $this->createCustomer('future-seller@example.com');
        $this->client->loginUser($customer, 'customer_context');

        $this->client->request('POST', '/become-seller', ['seller_request_form' => $this->applicationData()]);
        $this->client->request('POST', '/become-seller', ['seller_request_form' => $this->applicationData()]);

        self::assertCount(1, $this->em()->getRepository(SellerRequest::class)->findAll());
    }

    public function testAdminApprovesRequestAndSellerGetsActivationToken(): void
    {
        $sellerRequest = $this->applyAsCustomer();

        $admin = $this->createAdmin();
        $this->client->loginUser($admin, 'admin_context');

        $this->client->request('POST', '/admin/seller-requests/' . $sellerRequest->getId() . '/approve', [
            '_token' => $this->csrfTokenFromPage($sellerRequest),
        ]);

        $this->assertRedirectedTo('/admin/seller-requests');

        $this->em()->clear();
        $approved = $this->em()->getRepository(SellerRequest::class)->find($sellerRequest->getId());
        self::assertSame(SellerRequestStatus::Approved, $approved?->getStatus());
        self::assertNotNull($approved?->getReviewedAt());
        self::assertInstanceOf(Admin::class, $approved?->getReviewedBy());
        self::assertNotNull($approved?->getSeller()?->getActivationToken());
    }

    public function testAdminRejectsRequestWithReason(): void
    {
        $sellerRequest = $this->applyAsCustomer();

        $admin = $this->createAdmin();
        $this->client->loginUser($admin, 'admin_context');

        $this->client->request('POST', '/admin/seller-requests/' . $sellerRequest->getId() . '/reject', [
            '_token' => $this->csrfTokenFromPage($sellerRequest),
            'reason' => 'Нет документов',
        ]);

        $this->assertRedirectedTo('/admin/seller-requests');

        $this->em()->clear();
        $rejected = $this->em()->getRepository(SellerRequest::class)->find($sellerRequest->getId());
        self::assertSame(SellerRequestStatus::Rejected, $rejected?->getStatus());
        self::assertSame('Нет документов', $rejected?->getRejectionReason());
    }

    public function testApproveWithoutCsrfTokenIsDenied(): void
    {
        $sellerRequest = $this->applyAsCustomer();

        $admin = $this->createAdmin();
        $this->client->loginUser($admin, 'admin_context');

        $this->client->request('POST', '/admin/seller-requests/' . $sellerRequest->getId() . '/approve');

        self::assertResponseStatusCodeSame(403);
    }

    /**
     * @return array<string, string>
     */
    private function applicationData(): array
    {
        return [
            'shopName' => 'Магазин Ивана',
            'legalName' => 'ИП Иванов',
            'inn' => '1234567890',
            'contactPhone' => '+375291234567',
            'description' => 'Продаём всё',
        ];
    }

    private function applyAsCustomer(): SellerRequest
    {
        $customer = $this->createCustomer('flow@example.com');
        $this->client->loginUser($customer, 'customer_context');

        $this->client->request('POST', '/become-seller', [
            'seller_request_form' => $this->applicationData(),
        ]);

        $sellerRequest = $this->em()->getRepository(SellerRequest::class)->findOneBy([]);
        self::assertInstanceOf(SellerRequest::class, $sellerRequest);

        return $sellerRequest;
    }

    /**
     * Токен берём из реальной страницы: CSRF-хранилище привязано к сессии запроса,
     * поэтому сгенерировать его в обход HTTP-запроса нельзя.
     */
    private function csrfTokenFromPage(SellerRequest $sellerRequest): string
    {
        $crawler = $this->client->request('GET', '/admin/seller-requests');
        self::assertResponseIsSuccessful();

        $token = $crawler
            ->filter(sprintf('form[action$="/%d/approve"] input[name="_token"]', $sellerRequest->getId()))
            ->attr('value');

        self::assertNotNull($token, 'CSRF-токен кнопки "Одобрить" не найден на странице.');

        return $token;
    }
}
