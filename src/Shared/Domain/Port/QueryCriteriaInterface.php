<?php

declare(strict_types=1);

namespace App\Shared\Domain\Port;

interface QueryCriteriaInterface
{
    /**
     * @return array<string, string|int>
     */
    public function toQueryParameters(): array;
}
