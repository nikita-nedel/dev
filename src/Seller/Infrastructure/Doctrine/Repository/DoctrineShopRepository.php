<?php

declare(strict_types=1);

namespace App\Seller\Infrastructure\Doctrine\Repository;

use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Model\Shop;
use App\Seller\Domain\Repository\ShopRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Shop>
 */
final class DoctrineShopRepository extends ServiceEntityRepository implements ShopRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shop::class);
    }

    public function add(Shop $shop): void
    {
        $this->getEntityManager()->persist($shop);
    }

    public function findById(int $id): ?Shop
    {
        return $this->find($id);
    }

    public function findBySeller(Seller $seller): array
    {
        return $this->findBy(['seller' => $seller], ['createdAt' => 'ASC']);
    }
}
