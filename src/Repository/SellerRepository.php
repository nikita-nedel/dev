<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\Seller;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Seller>
 */
class SellerRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seller::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Seller) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findByCustomer(Customer $customer): ?Seller
    {
        return $this->findOneBy(['customer' => $customer]);
    }

    public function findByActivationToken(string $token): ?Seller
    {
        $seller = $this->findOneBy(['activationToken' => $token]);

        if (null === $seller || !$seller->isActivationTokenValid($token)) {
            return null;
        }

        return $seller;
    }
}
