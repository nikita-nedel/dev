<?php

declare(strict_types=1);

namespace App\Tests\Functional\Seller;

use App\Identity\Domain\Model\CustomerProfile;
use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Model\SellerRequest;
use App\Seller\Domain\Model\SellerRequestStatus;
use App\Tests\Functional\FunctionalTestCase;

final class SellerProfileTest extends FunctionalTestCase
{
    public function testSellerSeesOwnProfileAndStore(): void
    {
        $seller = $this->createSellerWithRequest();
        $this->client->loginUser($seller, 'seller_context');

        $this->client->request('GET', '/seller/profile');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.seller-hero', 'Иван Петров');
        self::assertSelectorTextContains('.seller-hero', 'Тестовый магазин');
        self::assertSelectorTextContains('#seller-personal', '+375291111111');
        self::assertSelectorTextContains('#seller-stores', 'Тестовый магазин');
    }

    public function testSellerCanUpdatePersonalAndStoreData(): void
    {
        $seller = $this->createSellerWithRequest();
        $this->client->loginUser($seller, 'seller_context');

        $this->client->request('POST', '/seller/profile', [
            'seller_profile_form' => [
                'firstName' => 'Пётр',
                'lastName' => 'Сидоров',
                'phone' => '+375292222222',
                'shopName' => 'Новый магазин',
                'legalName' => 'ООО Новый',
                'inn' => '1234567890',
                'contactPhone' => '+375293333333',
                'description' => 'Новое описание магазина.',
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

        $request = $this->em()->getRepository(SellerRequest::class)->findOneBy(['seller' => $updated]);
        self::assertInstanceOf(SellerRequest::class, $request);
        self::assertSame('Новый магазин', $request->getShopName());
        self::assertSame('1234567890', $request->getInn());
        self::assertSame('Новое описание магазина.', $request->getDescription());
    }

    public function testSellerWithoutStoreRequestCreatesItOnSave(): void
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

        $this->client->request('POST', '/seller/profile', [
            'seller_profile_form' => [
                'firstName' => 'Без',
                'lastName' => 'Магазина',
                'phone' => '+375294444444',
                'shopName' => 'Первый магазин',
                'contactPhone' => '+375295555555',
            ],
        ]);

        $this->assertRedirectedTo('/seller/profile');

        $this->em()->clear();
        $updated = $this->em()->getRepository(Seller::class)->find($seller->getId());
        self::assertInstanceOf(Seller::class, $updated);
        $request = $this->em()->getRepository(SellerRequest::class)->findOneBy(['seller' => $updated]);
        self::assertInstanceOf(SellerRequest::class, $request);
        self::assertSame('Первый магазин', $request->getShopName());
        self::assertSame(SellerRequestStatus::Approved, $request->getStatus());
    }

    public function testInvalidInnKeepsSellerOnEditForm(): void
    {
        $seller = $this->createSellerWithRequest();
        $this->client->loginUser($seller, 'seller_context');

        $this->client->request('POST', '/seller/profile', [
            'seller_profile_form' => [
                'firstName' => 'Иван',
                'lastName' => 'Петров',
                'phone' => '+375291111111',
                'shopName' => 'Тестовый магазин',
                'inn' => '123',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.seller-form__error, .seller-field .invalid-feedback', 'ИНН');

        $this->em()->clear();
        $unchanged = $this->em()->getRepository(Seller::class)->find($seller->getId());
        self::assertInstanceOf(Seller::class, $unchanged);
        $request = $this->em()->getRepository(SellerRequest::class)->findOneBy(['seller' => $unchanged]);
        self::assertInstanceOf(SellerRequest::class, $request);
        self::assertSame('Тестовый магазин', $request->getShopName());
    }

    private function createSellerWithRequest(): Seller
    {
        $customer = $this->createCustomer('seller-profile@example.com');
        $customer->setProfile(
            new CustomerProfile()
                ->setFirstName('Иван')
                ->setLastName('Петров')
                ->setPhone('+375291111111'),
        );

        $seller = $this->createSeller($customer);

        $request = new SellerRequest()
            ->setSeller($seller)
            ->setStatus(SellerRequestStatus::Approved)
            ->setShopName('Тестовый магазин')
            ->setLegalName('ООО Тест')
            ->setInn('1234567890')
            ->setContactPhone('+375292222222')
            ->setDescription('Магазин для тестов.');

        $this->em()->persist($request);
        $this->em()->flush();

        return $seller;
    }
}
