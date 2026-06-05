<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Export\Client\ClientListCriteria;
use App\Export\Contract\ExportCriteriaInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function createClientsQueryBuilder(?ClientListCriteria $criteria = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u');

        if (null !== $criteria) {
            $this->applyClientListCriteria($qb, $criteria);
        } else {
            $qb->orderBy('u.createdAt', 'DESC');
        }

        return $qb;
    }

    public function applyClientListCriteria(QueryBuilder $qb, ClientListCriteria $criteria): void
    {
        if (null !== $criteria->search) {
            $term = '%' . addcslashes(mb_strtolower($criteria->search), '%_') . '%';
            $qb->andWhere(
                'LOWER(u.email) LIKE :search OR LOWER(u.firstName) LIKE :search OR LOWER(u.lastName) LIKE :search OR u.phone LIKE :searchPhone',
            )
                ->setParameter('search', $term)
                ->setParameter('searchPhone', '%' . $criteria->search . '%');
        }

        if (null !== $criteria->periodDays) {
            $qb->andWhere('u.createdAt >= :registeredSince')
                ->setParameter('registeredSince', new \DateTimeImmutable('-' . $criteria->periodDays . ' days'));
        }

        // TODO: фильтр status — когда появится поле в User

        match ($criteria->sort) {
            'name-asc' => $qb->orderBy('u.lastName', 'ASC')->addOrderBy('u.firstName', 'ASC'),
            'name-desc' => $qb->orderBy('u.lastName', 'DESC')->addOrderBy('u.firstName', 'DESC'),
            'date-asc' => $qb->orderBy('u.createdAt', 'ASC'),
            default => $qb->orderBy('u.createdAt', 'DESC'),
        };
    }

    /**
     * @return iterable<User>
     */
    public function iterateClients(ClientListCriteria $criteria): iterable
    {
        return $this->createClientsQueryBuilder($criteria)->getQuery()->toIterable();
    }
}
