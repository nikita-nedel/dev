<?php

declare(strict_types=1);

namespace App\Shared\Domain\Port;

interface PasswordHasherInterface
{
    public function hash(object $user, string $plainPassword): string;
}
