<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RegisterAdminFromInvite;

use App\Identity\Domain\Model\AdminInvite;

final readonly class RegisterAdminFromInvite
{
    public function __construct(
        public AdminInvite $invite,
        public string $firstName,
        public string $lastName,
        public string $plainPassword,
    ) {
    }
}
