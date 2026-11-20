<?php

declare(strict_types=1);

namespace App\Export\Application\Command\RequestExport;

use App\Export\Domain\Model\ExportFormat;
use App\Export\Domain\Model\ExportResource;
use App\Identity\Domain\Model\Admin;

final readonly class RequestExport
{
    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        public ExportResource $resource,
        public ExportFormat $format,
        public array $filters = [],
        public ?Admin $requestedBy = null,
    ) {
    }
}
