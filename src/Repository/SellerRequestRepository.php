<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SellerRequest;
use App\Enum\SellerRequestStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SellerRequest>
 */
class SellerRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SellerRequest::class);
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
