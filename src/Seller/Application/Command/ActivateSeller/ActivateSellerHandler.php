<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\ActivateSeller;

use App\Shared\Domain\Port\PasswordHasherInterface;
use App\Shared\Domain\Port\TransactionManagerInterface;

/**
 * Use case: продавец активирует аккаунт по ссылке из письма.
 *
 * Хэширование — через порт (Application не знает про компонент Security),
 * сброс одноразового токена и установка пароля — в одной транзакции.
 */
final readonly class ActivateSellerHandler
{
    public function __construct(
        private PasswordHasherInterface $passwordHasher,
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(ActivateSeller $command): void
    {
        $seller = $command->seller;
        $hashedPassword = $this->passwordHasher->hash($seller, $command->plainPassword);

        $this->transactions->transactional(static function () use ($seller, $hashedPassword): void {
            $seller
                ->setPassword($hashedPassword)
                ->setIsActive(true)
                ->setActivationToken(null)
                ->setActivationTokenExpiresAt(null);
        });
    }
}
