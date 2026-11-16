<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Security;

use App\Identity\Domain\Model\Customer;
use App\Identity\Infrastructure\Doctrine\Repository\DoctrineCustomerRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class CustomerUserProvider extends EntityUserProvider
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly DoctrineCustomerRepository $customers,
    ) {
        parent::__construct($registry, Customer::class, 'email');
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Customer) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $this->customers->upgradePassword($user, $newHashedPassword);
    }
}
