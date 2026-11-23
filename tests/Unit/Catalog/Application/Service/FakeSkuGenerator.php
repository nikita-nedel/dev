<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Application\Service;

use App\Catalog\Domain\Service\SkuGeneratorInterface;
use App\Catalog\Domain\ValueObject\Sku;

final class FakeSkuGenerator implements SkuGeneratorInterface
{
    /**
     * @param list<Sku> $skus
     */
    public function __construct(private array $skus)
    {
    }

    public function generate(): Sku
    {
        $sku = array_shift($this->skus);

        if (null === $sku) {
            throw new \LogicException('Очередь SKU пуста.');
        }

        return $sku;
    }
}
