<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Seller;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SellerUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Seller) {
            return;
        }

        if (!$user->isActive() || null === $user->getPassword()) {
            throw new CustomUserMessageAccountStatusException('Аккаунт продавца не активирован. Перейдите по ссылке из письма.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
