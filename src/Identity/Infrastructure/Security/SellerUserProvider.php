<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Domain\Model\Seller;
use App\Identity\Infrastructure\Doctrine\Repository\DoctrineSellerRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class SellerUserProvider extends EntityUserProvider
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly DoctrineSellerRepository $sellers,
    ) {
        parent::__construct($registry, Seller::class, 'email');
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Seller) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $this->sellers->upgradePassword($user, $newHashedPassword);
    }
}
