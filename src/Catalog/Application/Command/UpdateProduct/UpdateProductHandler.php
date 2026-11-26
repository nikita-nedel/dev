<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateProduct;

use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use App\Catalog\Domain\ValueObject\Sku;
use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class UpdateProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(UpdateProduct $command): Product
    {
        $product = $command->product;
        $product->assertEditable();

        $sku = Sku::fromString($command->sku);

        if ($this->products->existsByShopAndSku($product->getShopId(), $sku->value(), $product->getId())) {
            throw new \RuntimeException('Товар с таким артикулом уже есть в магазине.');
        }

        $this->transactions->transactional(function () use ($product, $command, $sku): void {
            $product->rename($command->name);
            $product->changeSku($sku);
            $product->changePrice($command->price);
            $product->changeDescription($command->description);

            if ($product->getCategory()->getId() !== $command->category->getId()) {
                $product->changeCategory($command->category);
            }
        });

        return $product;
    }
}
