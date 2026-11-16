<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Model\AdminInvite;

interface AdminInviteRepositoryInterface
{
    public function add(AdminInvite $invite): void;

    public function findValidByToken(string $token): ?AdminInvite;
}
