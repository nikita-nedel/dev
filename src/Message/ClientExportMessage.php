<?php

declare(strict_types=1);

namespace App\Message;

use App\Enum\Export\ExportFormat;
use App\Enum\Export\ExportResource;

final readonly class ClientExportMessage
{
    public function __construct(
        public string $jobId,
        public ExportResource $resource,
        public ExportFormat $format,
        public array $filters,
    ) {
    }
}
