<?php

declare(strict_types=1);

namespace App\Tests\Functional\Seller;

use App\Identity\Domain\Model\CustomerProfile;
use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Model\Shop;
use App\Tests\Functional\FunctionalTestCase;

final class SellerProfileTest extends FunctionalTestCase
{
    public function testSellerSeesOwnProfileAndShop(): void
    {
        [$seller] = $this->createSellerWithShop();
        $this->client->loginUser($seller, 'seller_context');

        $this->client->request('GET', '/seller/profile');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.seller-hero', 'Иван Петров');
        self::assertSelectorTextContains('.seller-hero', 'Тестовый магазин');
        self::assertSelectorTextContains('#seller-personal', '+375291111111');
        self::assertSelectorTextContains('#seller-stores', 'Тестовый магазин');
        self::assertSelectorExists('[data-profile-editor="personal"]');
        self::assertSelectorExists('[data-profile-editor="shop"]');
    }

    public function testSellerCanUpdatePersonalDataWithoutChangingShop(): void
    {
        [$seller, $shop] = $this->createSellerWithShop();
        $this->client->loginUser($seller, 'seller_context');

        $this->client->request('POST', '/seller/profile/personal?shop=' . $shop->getId(), [
            'seller_personal_form' => [
                'firstName' => 'Пётр',
                'lastName' => 'Сидоров',
                'phone' => '+375292222222',
            ],
        ]);

        $this->assertRedirectedTo('/seller/profile');

        $this->em()->clear();
        $updated = $this->em()->getRepository(Seller::class)->find($seller->getId());
        self::assertInstanceOf(Seller::class, $updated);

        $customer = $updated->getCustomer();
        self::assertNotNull($customer);
        $customerProfile = $customer->getProfile();
        self::assertNotNull($customerProfile);

        self::assertSame('Пётр', $customerProfile->getFirstName());
        self::assertSame('Сидоров', $customerProfile->getLastName());
        self::assertSame('+375292222222', $customerProfile->getPhone());

        $unchangedShop = $this->em()->getRepository(Shop::class)->find($shop->getId());
        self::assertInstanceOf(Shop::class, $unchangedShop);
        self::assertSame('Тестовый магазин', $unchangedShop->getName());
        self::assertSame('+375292222222', $unchangedShop->getContactPhone());
    }

    public function testSellerCanUpdateShopWithoutChangingPersonalData(): void
    {
        [$seller, $shop] = $this->createSellerWithShop();
        $this->client->loginUser($seller, 'seller_context');

        $this->client->request('POST', '/seller/profile/shop?shop=' . $shop->getId(), [
            'seller_shop_form' => [
                'shopName' => 'Новый магазин',
                'legalName' => 'ООО Новый',
                'inn' => '1234567890',
                'contactPhone' => '+375293333333',
                'description' => 'Новое описание магазина.',
            ],
        ]);

        $this->assertRedirectedTo('/seller/profile');

        $this->em()->clear();
        $updatedShop = $this->em()->getRepository(Shop::class)->find($shop->getId());
        self::assertInstanceOf(Shop::class, $updatedShop);
        self::assertSame('Новый магазин', $updatedShop->getName());
        self::assertSame('1234567890', $updatedShop->getInn());
        self::assertSame('Новое описание магазина.', $updatedShop->getDescription());

        $updated = $this->em()->getRepository(Seller::class)->find($seller->getId());
        self::assertInstanceOf(Seller::class, $updated);
        $customerProfile = $updated->getCustomer()?->getProfile();
        self::assertNotNull($customerProfile);
        self::assertSame('Иван', $customerProfile->getFirstName());
        self::assertSame('+375291111111', $customerProfile->getPhone());
    }

    public function testSellerWithoutShopCreatesItOnSave(): void
    {
        $customer = $this->createCustomer('no-store@example.com');
        $customer->setProfile(
            new CustomerProfile()
                ->setFirstName('Без')
                ->setLastName('Магазина')
                ->setPhone('+375294444444'),
        );

        $seller = $this->createSeller($customer);
        $this->client->loginUser($seller, 'seller_context');

        $this->client->request('GET', '/seller/profile');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#seller-stores', 'Магазин без названия');

        $this->client->request('POST', '/seller/profile/shop', [
            'seller_shop_form' => [
                'shopName' => 'Первый магазин',
                'contactPhone' => '+375295555555',
            ],
        ]);

        $this->assertRedirectedTo('/seller/profile');

        $this->em()->clear();
        $updated = $this->em()->getRepository(Seller::class)->find($seller->getId());
        self::assertInstanceOf(Seller::class, $updated);
        $shop = $this->em()->getRepository(Shop::class)->findOneBy(['seller' => $updated]);
        self::assertInstanceOf(Shop::class, $shop);
        self::assertSame('Первый магазин', $shop->getName());
    }

    public function testInvalidInnKeepsSellerOnShopEditForm(): void
    {
        [$seller, $shop] = $this->createSellerWithShop();
        $this->client->loginUser($seller, 'seller_context');

        $this->client->request('POST', '/seller/profile/shop?shop=' . $shop->getId(), [
            'seller_shop_form' => [
                'shopName' => 'Тестовый магазин',
                'inn' => '123',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.seller-form__error, .seller-field .invalid-feedback', 'ИНН');

        $this->em()->clear();
        $unchangedShop = $this->em()->getRepository(Shop::class)->find($shop->getId());
        self::assertInstanceOf(Shop::class, $unchangedShop);
        self::assertSame('Тестовый магазин', $unchangedShop->getName());
    }

    /**
     * @return array{Seller, Shop}
     */
    private function createSellerWithShop(): array
    {
        $customer = $this->createCustomer('seller-profile@example.com');
        $customer->setProfile(
            new CustomerProfile()
                ->setFirstName('Иван')
                ->setLastName('Петров')
                ->setPhone('+375291111111'),
        );

        $seller = $this->createSeller($customer);
        $shop = Shop::open(
            seller: $seller,
            name: 'Тестовый магазин',
            legalName: 'ООО Тест',
            inn: '1234567890',
            description: 'Магазин для тестов.',
            contactPhone: '+375292222222',
        );

        $this->em()->persist($shop);
        $this->em()->flush();

        return [$seller, $shop];
    }
}
