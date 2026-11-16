<?php

declare(strict_types=1);

namespace App\Shared\Domain\Port;

interface PasswordHasherInterface
{
    /**
     * @param object $user
     * @param string $plainPassword
     * @return string
     */
    public function hash(object $user, string $plainPassword): string;
}
