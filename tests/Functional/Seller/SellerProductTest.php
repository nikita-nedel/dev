<?php

declare(strict_types=1);

namespace App\Tests\Functional\Seller;

use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Model\ProductStatus;
use App\Catalog\Domain\ValueObject\Sku;
use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Model\Shop;
use App\Shared\Domain\ValueObject\Money;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class SellerProductTest extends FunctionalTestCase
{
    public function testSellerCreatesEditsPublishesAndArchivesProduct(): void
    {
        [$seller, $shop] = $this->sellerWithShop('seller@example.com');
        $shopId = (int) $shop->getId();
        $category = $this->createCategory('Одежда');

        $this->client->loginUser($seller, 'seller_context');

        $crawler = $this->client->request('GET', '/seller/products/new');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Сохранить')->form([
            'product_form[shop]' => (string) $shopId,
            'product_form[category]' => (string) $category->getId(),
            'product_form[name]' => 'Футболка oversize',
            'product_form[sku]' => 'cl-001',
            'product_form[price]' => '12.99',
            'product_form[description]' => 'Плотный хлопок',
        ]);
        $this->client->submit($form);
        $this->assertRedirectedTo('/seller/products');

        $this->em()->clear();
        $product = $this->em()->getRepository(Product::class)->findOneBy(['sku' => 'CL-001']);
        self::assertInstanceOf(Product::class, $product);
        self::assertSame(ProductStatus::Draft, $product->getStatus());
        self::assertSame('12.99 BYN', $product->getPriceFormatted());

        $productId = (int) $product->getId();

        $crawler = $this->client->request('GET', '/seller/products/' . $productId . '/edit');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Сохранить')->form([
            'product_form[name]' => 'Футболка oversize v2',
            'product_form[price]' => '15.00',
        ]);
        $this->client->submit($form);
        $this->assertRedirectedTo('/seller/products');

        $this->em()->clear();
        $edited = $this->em()->getRepository(Product::class)->find($productId);
        self::assertInstanceOf(Product::class, $edited);
        self::assertSame('Футболка oversize v2', $edited->getName());
        self::assertSame('15.00 BYN', $edited->getPriceFormatted());

        $crawler = $this->client->request('GET', '/seller/products');
        $this->client->request('POST', '/seller/products/' . $productId . '/publish', [
            '_token' => $this->formToken($crawler, $productId, 'publish'),
        ]);
        $this->assertRedirectedTo('/seller/products');

        $this->em()->clear();
        self::assertSame(
            ProductStatus::Active,
            $this->em()->getRepository(Product::class)->find($productId)?->getStatus(),
        );

        $crawler = $this->client->request('GET', '/seller/products');
        $this->client->request('POST', '/seller/products/' . $productId . '/archive', [
            '_token' => $this->formToken($crawler, $productId, 'archive'),
        ]);
        $this->assertRedirectedTo('/seller/products');

        $this->em()->clear();
        self::assertSame(
            ProductStatus::Archived,
            $this->em()->getRepository(Product::class)->find($productId)?->getStatus(),
        );

        $this->client->request('GET', '/seller/products/' . $productId . '/edit');
        $this->assertRedirectedTo('/seller/products');
    }

    public function testSellerCannotEditProductOfAnotherSeller(): void
    {
        [, $shop] = $this->sellerWithShop('owner@example.com');
        [$otherSeller] = $this->sellerWithShop('other@example.com');

        $product = Product::create(
            shopId: (int) $shop->getId(),
            category: $this->createCategory('Одежда'),
            name: 'Чужой товар',
            sku: Sku::fromString('OWN-001'),
            price: Money::of(1000, 'BYN'),
        );
        $this->em()->persist($product);
        $this->em()->flush();

        $this->client->loginUser($otherSeller, 'seller_context');

        $this->client->request('GET', '/seller/products/' . $product->getId() . '/edit');
        self::assertResponseStatusCodeSame(404);

        $this->client->request('GET', '/seller/products');
        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('OWN-001', (string) $this->client->getResponse()->getContent());
    }

    public function testSellerCanGenerateSkuForOwnShop(): void
    {
        [$seller, $shop] = $this->sellerWithShop('sku-seller@example.com');
        $this->client->loginUser($seller, 'seller_context');

        $this->client->request('GET', '/seller/products/generate-sku?shopId=' . $shop->getId());

        self::assertResponseIsSuccessful();

        $payload = json_decode(
            (string) $this->client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        self::assertIsArray($payload);
        self::assertArrayHasKey('sku', $payload);
        self::assertMatchesRegularExpression('/^PRD-[A-HJ-NP-Z2-9]{8}$/', (string) $payload['sku']);
    }

    public function testSellerCannotGenerateSkuForAnotherShop(): void
    {
        [$seller] = $this->sellerWithShop('other@example.com');
        [, $shop] = $this->sellerWithShop('owner@example.com');

        $this->client->loginUser($seller, 'seller_context');

        $this->client->request('GET', '/seller/products/generate-sku?shopId=' . $shop->getId());

        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @return array{Seller, Shop}
     */
    private function sellerWithShop(string $email): array
    {
        $customer = $this->createCustomer($email);
        $seller = $this->createSeller($customer);

        $shop = Shop::open($seller, 'Магазин ' . $email);

        $em = $this->em();
        $em->persist($shop);
        $em->flush();

        return [$seller, $shop];
    }

    private function formToken(Crawler $crawler, int $productId, string $action): string
    {
        $token = $crawler
            ->filter(sprintf('form[action$="/%d/%s"] input[name="_token"]', $productId, $action))
            ->attr('value');

        self::assertNotNull($token, 'CSRF-токен не найден.');

        return $token;
    }
}
