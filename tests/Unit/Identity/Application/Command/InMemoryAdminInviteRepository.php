<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Application\Command;

use App\Identity\Domain\Model\AdminInvite;
use App\Identity\Domain\Repository\AdminInviteRepositoryInterface;

final class InMemoryAdminInviteRepository implements AdminInviteRepositoryInterface
{
    /**
     * @var list<AdminInvite>
     */
    public array $all = [];

    public function add(AdminInvite $invite): void
    {
        $this->all[] = $invite;
    }

    public function findValidByToken(string $token): ?AdminInvite
    {
        foreach ($this->all as $invite) {
            if ($invite->getToken() === $token) {
                return $invite;
            }
        }

        return null;
    }
}
