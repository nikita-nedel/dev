<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security;

use App\Shared\Domain\Port\PasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final readonly class SymfonyPasswordHasher implements PasswordHasherInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function hash(object $user, string $plainPassword): string
    {
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new \InvalidArgumentException(sprintf('Password hashing requires an instance of %s, %s given.', PasswordAuthenticatedUserInterface::class, $user::class));
        }

        return $this->passwordHasher->hashPassword($user, $plainPassword);
    }
}
