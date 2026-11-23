<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Model;

enum ProductStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Черновик',
            self::Active => 'Активен',
            self::Archived => 'В архиве',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'pending',
            self::Active => 'delivered',
            self::Archived => 'cancelled',
        };
    }
}
