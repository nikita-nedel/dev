<?php

declare(strict_types=1);

namespace App\Export\Application\Command\RunExport;

final readonly class RunExportMessage
{
    public function __construct(
        public string $exportId,
    ) {
    }
}
