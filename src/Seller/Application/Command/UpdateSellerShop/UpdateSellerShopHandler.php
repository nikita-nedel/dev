<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\UpdateSellerShop;

use App\Seller\Domain\Model\Shop;
use App\Seller\Domain\Repository\ShopRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UpdateSellerShopHandler
{
    public function __construct(
        private ShopRepositoryInterface $shops,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(UpdateSellerShop $command): Shop
    {
        $shop = $command->shop;
        $data = $command->data;

        if (null === $shop) {
            $shop = Shop::open(
                seller: $command->seller,
                name: trim($data->shopName),
                legalName: $this->nullable($data->legalName),
                inn: $this->nullable($data->inn),
                description: $this->nullable($data->description),
                contactPhone: $this->nullable($data->contactPhone),
            );

            $this->shops->add($shop);
        } else {
            if (!$shop->belongsTo($command->seller)) {
                throw new \RuntimeException('Магазин не принадлежит текущему продавцу.');
            }

            $shop->update(
                name: trim($data->shopName),
                legalName: $this->nullable($data->legalName),
                inn: $this->nullable($data->inn),
                description: $this->nullable($data->description),
                contactPhone: $this->nullable($data->contactPhone),
            );
        }

        $this->entityManager->flush();

        return $shop;
    }

    private function nullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return '' === $value ? null : $value;
    }
}
