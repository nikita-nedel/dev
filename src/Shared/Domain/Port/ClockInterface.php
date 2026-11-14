<?php

declare(strict_types=1);

namespace App\Shared\Domain\Port;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
