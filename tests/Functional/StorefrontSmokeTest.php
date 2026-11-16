<?php

declare(strict_types=1);

namespace App\Tests\Functional;

final class StorefrontSmokeTest extends FunctionalTestCase
{
    public function testHomePageRenders(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('body');
    }

    public function testCatalogPageRendersForKnownAndUnknownSlug(): void
    {
        $this->client->request('GET', '/catalog/odezhda');
        self::assertResponseIsSuccessful();

        $this->client->request('GET', '/catalog/unknown-category');
        self::assertResponseIsSuccessful();
    }

    public function testProductPageRendersForExistingAndFallbackId(): void
    {
        $this->client->request('GET', '/product/1');
        self::assertResponseIsSuccessful();

        $this->client->request('GET', '/product/9999');
        self::assertResponseIsSuccessful();
    }

    public function testCartPageRenders(): void
    {
        $this->client->request('GET', '/cart');

        self::assertResponseIsSuccessful();
    }

    public function testOrdersPageRendersForCustomer(): void
    {
        $customer = $this->createCustomer();
        $this->client->loginUser($customer, 'customer_context');

        $this->client->request('GET', '/orders');

        self::assertResponseIsSuccessful();
    }

    public function testProfilePageRendersForCustomer(): void
    {
        $customer = $this->createCustomer();
        $this->client->loginUser($customer, 'customer_context');

        $this->client->request('GET', '/profile');

        self::assertResponseIsSuccessful();
    }

    public function testSellerProductsPageRenders(): void
    {
        $customer = $this->createCustomer('seller@example.com');
        $seller = $this->createSeller($customer);
        $this->client->loginUser($seller, 'seller_context');

        $this->client->request('GET', '/seller/products');

        self::assertResponseIsSuccessful();
    }

    public function testAdminDashboardAndClientsPagesRender(): void
    {
        $admin = $this->createAdmin();
        $this->client->loginUser($admin, 'admin_context');

        $this->client->request('GET', '/admin/dashboard');
        self::assertResponseIsSuccessful();

        $this->client->request('GET', '/admin/client');
        self::assertResponseIsSuccessful();
    }
}
