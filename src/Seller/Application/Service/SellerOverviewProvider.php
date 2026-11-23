<?php

declare(strict_types=1);

namespace App\Seller\Application\Service;

use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Model\ProductStatus;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Model\Shop;
use App\Seller\Domain\Repository\ShopRepositoryInterface;

final readonly class SellerOverviewProvider
{
    public function __construct(
        private ShopRepositoryInterface $shops,
        private ProductRepositoryInterface $products,
    ) {
    }

    /**
     * @return array{
     *     shops: list<array{
     *         shop: Shop,
     *         total: int,
     *         active: int,
     *         draft: int,
     *         archived: int,
     *         recentProducts: list<Product>
     *     }>,
     *     totals: array{total: int, active: int, draft: int, archived: int}
     * }
     */
    public function forSeller(Seller $seller): array
    {
        $shops = $this->shops->findBySeller($seller);
        $shopIds = [];

        foreach ($shops as $shop) {
            $shopId = $shop->getId();

            if (null !== $shopId) {
                $shopIds[] = $shopId;
            }
        }

        $products = [];

        if ([] !== $shopIds) {
            /** @var list<Product> $products */
            $products = $this->products
                ->createForShopIdsQueryBuilder($shopIds)
                ->getQuery()
                ->getResult();
        }

        $productsByShop = [];

        foreach ($products as $product) {
            $productsByShop[$product->getShopId()][] = $product;
        }

        $shopRows = [];
        $totals = ['total' => 0, 'active' => 0, 'draft' => 0, 'archived' => 0];

        foreach ($shops as $shop) {
            $shopId = $shop->getId();
            $shopProducts = null !== $shopId ? ($productsByShop[$shopId] ?? []) : [];

            $active = 0;
            $draft = 0;
            $archived = 0;

            foreach ($shopProducts as $product) {
                match ($product->getStatus()) {
                    ProductStatus::Active => ++$active,
                    ProductStatus::Draft => ++$draft,
                    ProductStatus::Archived => ++$archived,
                };
            }

            $row = [
                'shop' => $shop,
                'total' => count($shopProducts),
                'active' => $active,
                'draft' => $draft,
                'archived' => $archived,
                'recentProducts' => array_slice($shopProducts, 0, 3),
            ];

            $shopRows[] = $row;

            $totals['total'] += $row['total'];
            $totals['active'] += $row['active'];
            $totals['draft'] += $row['draft'];
            $totals['archived'] += $row['archived'];
        }

        return [
            'shops' => $shopRows,
            'totals' => $totals,
        ];
    }
}
