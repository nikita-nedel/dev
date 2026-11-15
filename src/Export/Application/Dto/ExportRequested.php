<?php

declare(strict_types=1);

namespace App\Export\Application\Dto;

final readonly class ExportRequested
{
    public function __construct(
        public string $jobId,
        public string $exportId,
    ) {
    }
}
