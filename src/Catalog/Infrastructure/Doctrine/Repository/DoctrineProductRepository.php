<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Doctrine\Repository;

use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Repository\ProductRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
final class DoctrineProductRepository extends ServiceEntityRepository implements ProductRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function add(Product $product): void
    {
        $this->getEntityManager()->persist($product);
    }

    public function findById(int $id): ?Product
    {
        return $this->find($id);
    }

    public function createForShopIdsQueryBuilder(array $shopIds): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC');

        if ([] === $shopIds) {
            return $qb->andWhere('1 = 0');
        }

        return $qb
            ->andWhere('p.shopId IN (:shopIds)')
            ->setParameter('shopIds', $shopIds);
    }

    public function existsByShopAndSku(int $shopId, string $sku, ?int $excludeProductId = null): bool
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.shopId = :shopId')
            ->andWhere('p.sku = :sku')
            ->setParameter('shopId', $shopId)
            ->setParameter('sku', $sku);

        if (null !== $excludeProductId) {
            $qb
                ->andWhere('p.id <> :excludeId')
                ->setParameter('excludeId', $excludeProductId);
        }

        return 0 < (int) $qb->getQuery()->getSingleScalarResult();
    }
}
