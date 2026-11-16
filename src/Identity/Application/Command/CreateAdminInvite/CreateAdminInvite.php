<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\CreateAdminInvite;

use App\Identity\Domain\Model\Admin;

final readonly class CreateAdminInvite
{
    public function __construct(
        public string $email,
        public ?Admin $createdBy = null,
        public int $ttlDays = 2,
    ) {
    }
}
