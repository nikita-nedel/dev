<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Domain\Model\Admin;
use App\Identity\Infrastructure\Doctrine\Repository\DoctrineAdminRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class AdminUserProvider extends EntityUserProvider
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly DoctrineAdminRepository $admins,
    ) {
        parent::__construct($registry, Admin::class, 'email');
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Admin) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $this->admins->upgradePassword($user, $newHashedPassword);
    }
}
