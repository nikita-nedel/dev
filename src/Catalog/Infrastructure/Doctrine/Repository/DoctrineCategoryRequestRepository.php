<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Doctrine\Repository;

use App\Catalog\Domain\Model\CategoryRequest;
use App\Catalog\Domain\Model\CategoryRequestStatus;
use App\Catalog\Domain\Repository\CategoryRequestRepositoryInterface;
use App\Identity\Domain\Model\Seller;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategoryRequest>
 */
final class DoctrineCategoryRequestRepository extends ServiceEntityRepository implements CategoryRequestRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryRequest::class);
    }

    public function add(CategoryRequest $request): void
    {
        $this->getEntityManager()->persist($request);
    }

    public function findById(int $id): ?CategoryRequest
    {
        return $this->find($id);
    }

    public function createListQueryBuilder(?CategoryRequestStatus $status = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.seller', 's')
            ->addSelect('s')
            ->leftJoin('s.customer', 'c')
            ->addSelect('c')
            ->leftJoin('c.profile', 'cp')
            ->addSelect('cp')
            ->leftJoin('r.parent', 'p')
            ->addSelect('p')
            ->orderBy('r.createdAt', 'DESC');

        if (null !== $status) {
            $qb
                ->andWhere('r.status = :status')
                ->setParameter('status', $status);
        }

        return $qb;
    }

    public function findBySeller(Seller $seller): array
    {
        /** @var list<CategoryRequest> $requests */
        $requests = $this->createQueryBuilder('r')
            ->leftJoin('r.parent', 'p')
            ->addSelect('p')
            ->andWhere('r.seller = :seller')
            ->setParameter('seller', $seller)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $requests;
    }

    public function existsPending(Seller $seller, string $name, ?int $parentId): bool
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.seller = :seller')
            ->andWhere('r.status = :status')
            ->andWhere('LOWER(r.name) = LOWER(:name)')
            ->setParameter('seller', $seller)
            ->setParameter('status', CategoryRequestStatus::Pending)
            ->setParameter('name', $name);

        if (null === $parentId) {
            $qb->andWhere('r.parent IS NULL');
        } else {
            $qb
                ->andWhere('IDENTITY(r.parent) = :parentId')
                ->setParameter('parentId', $parentId);
        }

        return 0 < (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countPendingBySeller(Seller $seller): int
    {
        $count = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.seller = :seller')
            ->andWhere('r.status = :status')
            ->setParameter('seller', $seller)
            ->setParameter('status', CategoryRequestStatus::Pending)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count;
    }

    public function countPending(): int
    {
        $count = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.status = :status')
            ->setParameter('status', CategoryRequestStatus::Pending)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count;
    }
}
