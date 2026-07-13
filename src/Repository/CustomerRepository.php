<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Customer;
use App\Export\Client\ClientListCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Customer) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function createClientsQueryBuilder(?ClientListCriteria $criteria = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.profile', 'p')
            ->addSelect('p');

        if (null !== $criteria) {
            $this->applyClientListCriteria($qb, $criteria);
        } else {
            $qb->orderBy('c.createdAt', 'DESC');
        }

        return $qb;
    }

    public function applyClientListCriteria(QueryBuilder $qb, ClientListCriteria $criteria): void
    {
        if (null !== $criteria->search) {
            $term = '%' . addcslashes(mb_strtolower($criteria->search), '%_') . '%';
            $qb->andWhere(
                'LOWER(c.email) LIKE :search OR LOWER(p.firstName) LIKE :search OR LOWER(p.lastName) LIKE :search OR p.phone LIKE :searchPhone',
            )
                ->setParameter('search', $term)
                ->setParameter('searchPhone', '%' . $criteria->search . '%');
        }

        if (null !== $criteria->periodDays) {
            $qb->andWhere('c.createdAt >= :registeredSince')
                ->setParameter('registeredSince', new \DateTimeImmutable('-' . $criteria->periodDays . ' days'));
        }

        match ($criteria->sort) {
            'name-asc' => $qb->orderBy('p.lastName', 'ASC')->addOrderBy('p.firstName', 'ASC'),
            'name-desc' => $qb->orderBy('p.lastName', 'DESC')->addOrderBy('p.firstName', 'DESC'),
            'date-asc' => $qb->orderBy('c.createdAt', 'ASC'),
            default => $qb->orderBy('c.createdAt', 'DESC'),
        };
    }

    /**
     * @return iterable<Customer>
     */
    public function iterateClients(ClientListCriteria $criteria): iterable
    {
        return $this->createClientsQueryBuilder($criteria)->getQuery()->toIterable();
    }
}
