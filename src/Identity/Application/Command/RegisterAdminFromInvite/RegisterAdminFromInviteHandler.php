<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RegisterAdminFromInvite;

use App\Identity\Domain\Model\Admin;
use App\Identity\Domain\Model\AdminProfile;
use App\Identity\Domain\Repository\AdminRepositoryInterface;
use App\Shared\Domain\Port\PasswordHasherInterface;
use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class RegisterAdminFromInviteHandler
{
    public function __construct(
        private AdminRepositoryInterface $admins,
        private PasswordHasherInterface $passwordHasher,
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(RegisterAdminFromInvite $command): Admin
    {
        $admin = (new Admin())
            ->setEmail($command->invite->getEmail())
            ->setRoles(['ROLE_ADMIN'])
            ->setIsActive(true);

        $admin->setPassword($this->passwordHasher->hash($admin, $command->plainPassword));
        $admin->setProfile(
            (new AdminProfile())
                ->setFirstName($command->firstName)
                ->setLastName($command->lastName),
        );

        $this->transactions->transactional(function () use ($command, $admin): void {
            $command->invite->markUsed();
            $this->admins->add($admin);
        });

        return $admin;
    }
}
