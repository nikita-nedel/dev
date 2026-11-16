<?php

declare(strict_types=1);

namespace App\Seller\Infrastructure\Doctrine\Repository;

use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Model\SellerRequest;
use App\Seller\Domain\Model\SellerRequestStatus;
use App\Seller\Domain\Repository\SellerRequestRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Doctrine-реализация порта заявок на продажу.
 *
 * @extends ServiceEntityRepository<SellerRequest>
 */
final class DoctrineSellerRequestRepository extends ServiceEntityRepository implements SellerRequestRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SellerRequest::class);
    }

    public function add(SellerRequest $request): void
    {
        $this->getEntityManager()->persist($request);
    }

    public function findBySeller(Seller $seller): ?SellerRequest
    {
        return $this->findOneBy(['seller' => $seller]);
    }

    public function createListQueryBuilder(?SellerRequestStatus $status = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.seller', 's')
            ->addSelect('s')
            ->leftJoin('s.customer', 'c')
            ->addSelect('c')
            ->leftJoin('c.profile', 'cp')
            ->addSelect('cp')
            ->orderBy('r.createdAt', 'DESC');

        if (null !== $status) {
            $qb->andWhere('r.status = :status')
                ->setParameter('status', $status);
        }

        return $qb;
    }
}
