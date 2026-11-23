<?php

declare(strict_types=1);

namespace App\Tests\Unit\Seller\Domain\Model;

use App\Identity\Domain\Model\Customer;
use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Model\Shop;
use PHPUnit\Framework\TestCase;

final class ShopTest extends TestCase
{
    public function testOpenNormalizesInput(): void
    {
        $seller = $this->seller();

        $shop = Shop::open(
            seller: $seller,
            name: '  Магазин Ивана  ',
            legalName: '  ИП Иванов ',
            inn: ' 1234567890 ',
            description: '  Описание ',
            contactPhone: ' +375291234567 ',
        );

        self::assertSame($seller, $shop->getSeller());
        self::assertSame('Магазин Ивана', $shop->getName());
        self::assertSame('ИП Иванов', $shop->getLegalName());
        self::assertSame('1234567890', $shop->getInn());
        self::assertSame('Описание', $shop->getDescription());
        self::assertSame('+375291234567', $shop->getContactPhone());
        self::assertSame($shop->getCreatedAt(), $shop->getUpdatedAt());
        self::assertTrue($shop->belongsTo($seller));
    }

    public function testOpenRejectsEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Shop::open($this->seller(), '   ');
    }

    public function testOpenRejectsTooLongName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Shop::open($this->seller(), str_repeat('a', Shop::NAME_MAX_LENGTH + 1));
    }

    public function testOpenRejectsInvalidInn(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Shop::open($this->seller(), 'Магазин', inn: '12345');
    }

    public function testUpdateReplacesDetailsAndNormalizesBlanks(): void
    {
        $shop = Shop::open($this->seller(), 'Старое имя', 'ИП Старый', '1234567890');

        $shop->update('Новое имя', null, null, '  ', '  ');

        self::assertSame('Новое имя', $shop->getName());
        self::assertNull($shop->getLegalName());
        self::assertNull($shop->getInn());
        self::assertNull($shop->getDescription());
        self::assertNull($shop->getContactPhone());
    }

    public function testBelongsToAnotherSellerIsFalse(): void
    {
        $shop = Shop::open($this->seller('seller-1@example.com'), 'Магазин');

        self::assertFalse($shop->belongsTo($this->seller('seller-2@example.com')));
    }

    private function seller(string $email = 'seller@example.com'): Seller
    {
        $customer = new Customer()->setEmail($email);

        return new Seller()
            ->setEmail($email)
            ->setCustomer($customer)
            ->setRoles(['ROLE_SELLER']);
    }
}
