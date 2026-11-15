<?php

declare(strict_types=1);

namespace App\Export\Application\Port;

interface ExportCriteriaInterface
{
    /**
     * @return array<string, string|int>
     */
    public function toQueryParameters(): array;
}
