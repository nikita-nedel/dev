<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Admin;
use App\Entity\AdminInvite;
use App\Entity\AdminProfile;
use App\Repository\AdminInviteRepository;
use Doctrine\ORM\EntityManagerInterface;

final class AdminInviteService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminInviteRepository $adminInviteRepository,
    ) {
    }

    public function createInvite(string $email, ?Admin $createdBy = null, int $ttlDays = 2): AdminInvite
    {
        $invite = new AdminInvite(
            token: bin2hex(random_bytes(32)),
            email: $email,
            expiresAt: new \DateTimeImmutable('+' . $ttlDays . ' days'),
            createdBy: $createdBy,
        );

        $this->entityManager->persist($invite);
        $this->entityManager->flush();

        return $invite;
    }

    public function findValidInvite(string $token): ?AdminInvite
    {
        return $this->adminInviteRepository->findValidByToken($token);
    }

    public function registerAdminFromInvite(AdminInvite $invite, string $firstName, string $lastName, string $hashedPassword): Admin
    {
        $profile = new AdminProfile()
            ->setFirstName($firstName)
            ->setLastName($lastName);

        $admin = new Admin()
            ->setEmail($invite->getEmail())
            ->setPassword($hashedPassword)
            ->setRoles(['ROLE_ADMIN'])
            ->setIsActive(true)
            ->setProfile($profile);

        $invite->markUsed();

        $this->entityManager->persist($admin);
        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        return $admin;
    }
}
