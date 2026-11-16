<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\CreateAdminInvite;

use App\Identity\Domain\Model\AdminInvite;
use App\Identity\Domain\Repository\AdminInviteRepositoryInterface;
use App\Shared\Domain\Port\ClockInterface;
use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class CreateAdminInviteHandler
{
    public function __construct(
        private AdminInviteRepositoryInterface $invites,
        private ClockInterface $clock,
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(CreateAdminInvite $command): AdminInvite
    {
        $invite = new AdminInvite(
            token: bin2hex(random_bytes(32)),
            email: $command->email,
            expiresAt: $this->clock->now()->modify(sprintf('+%d days', $command->ttlDays)),
            createdBy: $command->createdBy,
        );

        $this->transactions->transactional(fn () => $this->invites->add($invite));

        return $invite;
    }
}
