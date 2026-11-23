<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service;

use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use App\Catalog\Domain\Service\SkuGeneratorInterface;
use App\Catalog\Domain\ValueObject\Sku;

final readonly class GenerateSkuService
{
    private const int MAX_ATTEMPTS = 10;

    public function __construct(
        private SkuGeneratorInterface $generator,
        private ProductRepositoryInterface $products,
    ) {
    }

    public function generate(int $shopId): Sku
    {
        for ($i = 0; $i < self::MAX_ATTEMPTS; ++$i) {
            $sku = $this->generator->generate();

            if (!$this->products->existsByShopAndSku($shopId, $sku->value())) {
                return $sku;
            }
        }

        throw new \RuntimeException('Не удалось сгенерировать уникальный артикул. Попробуйте ещё раз.');
    }
}
