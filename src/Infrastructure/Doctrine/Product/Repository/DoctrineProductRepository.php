<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Product\Repository;

use App\Domain\Product\Repository\ProductRepositoryInterface;

class DoctrineProductRepository implements ProductRepositoryInterface
{
    public function test(): string
    {
        return 'test';
    }
}
