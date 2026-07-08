<?php

declare(strict_types=1);

namespace App\Export\Contract;

interface ExportCriteriaInterface
{
    public function toQueryParameters(): array;
}
