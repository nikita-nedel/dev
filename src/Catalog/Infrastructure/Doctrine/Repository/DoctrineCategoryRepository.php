<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Doctrine\Repository;

use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Model\Product;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
final class DoctrineCategoryRepository extends ServiceEntityRepository implements CategoryRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function add(Category $category): void
    {
        $this->getEntityManager()->persist($category);
    }

    public function findById(int $id): ?Category
    {
        return $this->find($id);
    }

    public function existsBySlug(string $slug): bool
    {
        $count = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getSingleScalarResult();

        return 0 < (int) $count;
    }

    public function existsByName(string $name, ?int $parentId, ?int $excludeCategoryId = null): bool
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('LOWER(c.name) = LOWER(:name)')
            ->setParameter('name', $name);

        if (null === $parentId) {
            $qb->andWhere('c.parent IS NULL');
        } else {
            $qb
                ->andWhere('IDENTITY(c.parent) = :parentId')
                ->setParameter('parentId', $parentId);
        }

        if (null !== $excludeCategoryId) {
            $qb
                ->andWhere('c.id <> :excludeId')
                ->setParameter('excludeId', $excludeCategoryId);
        }

        return 0 < (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findRootsWithChildren(bool $onlyActive = false): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.children', 'ch')
            ->addSelect('ch')
            ->andWhere('c.parent IS NULL')
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('c.name', 'ASC');

        if ($onlyActive) {
            $qb->andWhere('c.isActive = true');
        }

        /** @var list<Category> $roots */
        $roots = $qb->getQuery()->getResult();

        return $roots;
    }

    public function countProducts(Category $category): int
    {
        $count = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Product::class, 'p')
            ->andWhere('p.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count;
    }

    public function countProductsByCategoryId(): array
    {
        /** @var list<array{categoryId: int|string, productsCount: int|string}> $rows */
        $rows = $this->getEntityManager()->createQueryBuilder()
            ->select('IDENTITY(p.category) AS categoryId', 'COUNT(p.id) AS productsCount')
            ->from(Product::class, 'p')
            ->groupBy('p.category')
            ->getQuery()
            ->getArrayResult();

        $counts = [];

        foreach ($rows as $row) {
            $counts[(int) $row['categoryId']] = (int) $row['productsCount'];
        }

        return $counts;
    }
}
