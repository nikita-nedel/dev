<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateProduct;

use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use App\Catalog\Domain\ValueObject\Sku;
use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class CreateProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(CreateProduct $command): Product
    {
        $sku = Sku::fromString($command->sku);

        if ($this->products->existsByShopAndSku($command->shopId, $sku->value())) {
            throw new \RuntimeException('Товар с таким артикулом уже есть в магазине.');
        }

        $product = Product::create(
            shopId: $command->shopId,
            category: $command->category,
            name: $command->name,
            sku: $sku,
            price: $command->price,
            description: $command->description,
        );

        $this->transactions->transactional(fn () => $this->products->add($product));

        return $product;
    }
}
