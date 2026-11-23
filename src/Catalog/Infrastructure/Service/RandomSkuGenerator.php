<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Service;

use App\Catalog\Domain\Service\SkuGeneratorInterface;
use App\Catalog\Domain\ValueObject\Sku;

final readonly class RandomSkuGenerator implements SkuGeneratorInterface
{
    private const string PREFIX = 'PRD-';

    // Без похожих символов: I, O, 0, 1.
    private const string ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private const int RANDOM_LENGTH = 8;

    public function generate(): Sku
    {
        $suffix = '';

        for ($i = 0; $i < self::RANDOM_LENGTH; ++$i) {
            $suffix .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return Sku::fromString(self::PREFIX . $suffix);
    }
}
