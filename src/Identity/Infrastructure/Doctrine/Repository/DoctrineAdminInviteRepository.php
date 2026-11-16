<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Doctrine\Repository;

use App\Identity\Domain\Model\AdminInvite;
use App\Identity\Domain\Repository\AdminInviteRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdminInvite>
 */
final class DoctrineAdminInviteRepository extends ServiceEntityRepository implements AdminInviteRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminInvite::class);
    }

    public function add(AdminInvite $invite): void
    {
        $this->getEntityManager()->persist($invite);
    }

    public function findValidByToken(string $token): ?AdminInvite
    {
        $invite = $this->findOneBy(['token' => $token]);

        if (null === $invite || !$invite->isValid()) {
            return null;
        }

        return $invite;
    }
}
