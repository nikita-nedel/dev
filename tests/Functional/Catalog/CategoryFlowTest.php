<?php

declare(strict_types=1);

namespace App\Tests\Functional\Catalog;

use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Model\CategoryRequest;
use App\Catalog\Domain\Model\CategoryRequestStatus;
use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\ValueObject\Sku;
use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Model\Shop;
use App\Shared\Domain\ValueObject\Money;
use App\Tests\Functional\FunctionalTestCase;

final class CategoryFlowTest extends FunctionalTestCase
{
    public function testAdminCreatesRootAndSubcategoryDirectly(): void
    {
        $admin = $this->createAdmin();
        $this->client->loginUser($admin, 'admin_context');

        $this->createCategoryThroughForm('Электроника', null, 'bi-phone');

        $root = $this->categoryByName('Электроника');
        self::assertTrue($root->isActive());
        self::assertNull($root->getParent());

        $this->createCategoryThroughForm('Умный дом', $root);

        $child = $this->categoryByName('Умный дом');
        self::assertSame($root->getId(), $child->getParent()?->getId());
        self::assertCount(0, $this->em()->getRepository(CategoryRequest::class)->findAll());
    }

    public function testSellerRequestsSubcategoryAndAdminApproves(): void
    {
        $root = $this->createCategory('Электроника');
        [$seller] = $this->sellerWithShop('request-seller@example.com');

        $this->client->loginUser($seller, 'seller_context');

        $crawler = $this->client->request('GET', '/seller/categories/request');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Отправить на проверку')->form([
            'category_request_form[name]' => 'Умный дом',
            'category_request_form[comment]' => 'Нужна для лампочек',
        ]);
        $form['category_request_form[parent]'] = (string) $root->getId();
        $this->client->submit($form);
        $this->assertRedirectedTo('/seller/categories');

        $request = $this->em()->getRepository(CategoryRequest::class)->findOneBy([]);
        self::assertInstanceOf(CategoryRequest::class, $request);
        self::assertSame(CategoryRequestStatus::Pending, $request->getStatus());
        $requestId = (int) $request->getId();

        $admin = $this->createAdmin();
        $this->client->loginUser($admin, 'admin_context');

        $token = $this->approveToken($requestId);

        $this->client->request('POST', '/admin/category-requests/' . $requestId . '/approve', [
            '_token' => $token,
        ]);
        $this->assertRedirectedTo('/admin/category-requests');

        $this->em()->clear();

        $approved = $this->em()->getRepository(CategoryRequest::class)->find($requestId);
        self::assertInstanceOf(CategoryRequest::class, $approved);
        self::assertSame(CategoryRequestStatus::Approved, $approved->getStatus());

        $category = $this->em()->getRepository(Category::class)->findOneBy(['name' => 'Умный дом']);
        self::assertInstanceOf(Category::class, $category);
        self::assertSame($root->getId(), $category->getParent()?->getId());
        self::assertSame($category->getId(), $approved->getCreatedCategory()?->getId());
    }

    public function testAdminRejectsRequestWithReason(): void
    {
        [$seller] = $this->sellerWithShop('reject-seller@example.com');
        $this->client->loginUser($seller, 'seller_context');

        $crawler = $this->client->request('GET', '/seller/categories/request');
        $form = $crawler->selectButton('Отправить на проверку')->form([
            'category_request_form[name]' => 'Одежда',
        ]);
        $this->client->submit($form);
        $this->assertRedirectedTo('/seller/categories');

        $request = $this->em()->getRepository(CategoryRequest::class)->findOneBy([]);
        self::assertInstanceOf(CategoryRequest::class, $request);
        $requestId = (int) $request->getId();

        $admin = $this->createAdmin();
        $this->client->loginUser($admin, 'admin_context');

        $crawler = $this->client->request('GET', '/admin/category-requests');
        $token = $crawler
            ->filter(sprintf('form[action$="/%d/reject"] input[name="_token"]', $requestId))
            ->attr('value');
        self::assertNotNull($token);

        $this->client->request('POST', '/admin/category-requests/' . $requestId . '/reject', [
            '_token' => $token,
            'reason' => 'Дубликат',
        ]);
        $this->assertRedirectedTo('/admin/category-requests');

        $this->em()->clear();
        $rejected = $this->em()->getRepository(CategoryRequest::class)->find($requestId);
        self::assertInstanceOf(CategoryRequest::class, $rejected);
        self::assertSame(CategoryRequestStatus::Rejected, $rejected->getStatus());
        self::assertSame('Дубликат', $rejected->getRejectionReason());
    }

    public function testApproveFailsWhenParentGotProductsWhileRequestWasPending(): void
    {
        $root = $this->createCategory('Электроника');
        [$seller, $shop] = $this->sellerWithShop('products-seller@example.com');

        $this->client->loginUser($seller, 'seller_context');
        $crawler = $this->client->request('GET', '/seller/categories/request');
        $form = $crawler->selectButton('Отправить на проверку')->form([
            'category_request_form[name]' => 'Умный дом',
        ]);
        $form['category_request_form[parent]'] = (string) $root->getId();
        $this->client->submit($form);
        $this->assertRedirectedTo('/seller/categories');

        $request = $this->em()->getRepository(CategoryRequest::class)->findOneBy([]);
        self::assertInstanceOf(CategoryRequest::class, $request);
        $requestId = (int) $request->getId();

        $root = $this->em()->getRepository(Category::class)->find((int) $root->getId());
        self::assertInstanceOf(Category::class, $root);

        $product = Product::create(
            shopId: (int) $shop->getId(),
            category: $root,
            name: 'Лампа',
            sku: Sku::fromString('LAMP-001'),
            price: Money::of(1000, 'BYN'),
        );
        $this->em()->persist($product);
        $this->em()->flush();

        $admin = $this->createAdmin();
        $this->client->loginUser($admin, 'admin_context');

        $this->client->request('POST', '/admin/category-requests/' . $requestId . '/approve', [
            '_token' => $this->approveToken($requestId),
        ]);
        $this->assertRedirectedTo('/admin/category-requests');

        $this->client->followRedirect();
        self::assertStringContainsString(
            'В категории уже есть товары',
            (string) $this->client->getResponse()->getContent(),
        );

        $this->em()->clear();
        $stillPending = $this->em()->getRepository(CategoryRequest::class)->find($requestId);
        self::assertSame(CategoryRequestStatus::Pending, $stillPending?->getStatus());
        self::assertNull($this->em()->getRepository(Category::class)->findOneBy(['name' => 'Умный дом']));
    }

    public function testProductFormOffersOnlyLeafCategories(): void
    {
        $root = $this->createCategory('Электроника');
        $child = $this->createCategory('Умный дом', $root);
        $rootId = (int) $root->getId();
        $childId = (int) $child->getId();

        $this->em()->clear();

        [$seller] = $this->sellerWithShop('leaf-seller@example.com');

        $this->client->loginUser($seller, 'seller_context');

        $crawler = $this->client->request('GET', '/seller/products/new');
        self::assertResponseIsSuccessful();

        self::assertCount(
            1,
            $crawler->filter(sprintf('select[name="product_form[category]"] option[value="%d"]', $childId)),
        );
        self::assertCount(
            0,
            $crawler->filter(sprintf('select[name="product_form[category]"] option[value="%d"]', $rootId)),
        );
    }

    public function testProductCannotBeCreatedInCategoryWithChildren(): void
    {
        $root = $this->createCategory('Электроника');
        $this->createCategory('Умный дом', $root);
        $rootId = (int) $root->getId();

        $this->em()->clear();

        [$seller, $shop] = $this->sellerWithShop('invalid-category@example.com');
        $shopId = (int) $shop->getId();

        $this->client->loginUser($seller, 'seller_context');

        $this->client->request('GET', '/seller/products/new');
        self::assertResponseIsSuccessful();

        $this->client->request('POST', '/seller/products/new', [
            'product_form' => [
                'shop' => (string) $shopId,
                'category' => (string) $rootId,
                'name' => 'Лампа',
                'sku' => 'LAMP-002',
                'price' => '10.00',
            ],
        ]);

        self::assertResponseStatusCodeSame(200);
        self::assertCount(0, $this->em()->getRepository(Product::class)->findAll());
    }

    private function createCategoryThroughForm(string $name, ?Category $parent, ?string $icon = null): void
    {
        $crawler = $this->client->request('GET', '/admin/categories/new');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Сохранить')->form([
            'category_form[name]' => $name,
            'category_form[icon]' => $icon ?? '',
            'category_form[sortOrder]' => '0',
        ]);

        if (null !== $parent) {
            $form['category_form[parent]'] = (string) $parent->getId();
        }

        $this->client->submit($form);
        $this->assertRedirectedTo('/admin/categories');
    }

    private function categoryByName(string $name): Category
    {
        $this->em()->clear();

        $category = $this->em()->getRepository(Category::class)->findOneBy(['name' => $name]);
        self::assertInstanceOf(Category::class, $category);

        return $category;
    }

    private function approveToken(int $requestId): string
    {
        $crawler = $this->client->request('GET', '/admin/category-requests');
        self::assertResponseIsSuccessful();

        $token = $crawler
            ->filter(sprintf('form[action$="/%d/approve"] input[name="_token"]', $requestId))
            ->attr('value');
        self::assertNotNull($token);

        return $token;
    }

    /**
     * @return array{Seller, Shop}
     */
    private function sellerWithShop(string $email): array
    {
        $customer = $this->createCustomer($email);
        $seller = $this->createSeller($customer);

        $shop = Shop::open($seller, 'Магазин ' . $email);

        $this->em()->persist($shop);
        $this->em()->flush();

        return [$seller, $shop];
    }
}
