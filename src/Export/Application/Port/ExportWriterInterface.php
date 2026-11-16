<?php

declare(strict_types=1);

namespace App\Export\Application\Port;

use App\Export\Application\Dto\ExportContext;
use App\Export\Domain\Model\ExportFormat;

interface ExportWriterInterface
{
    public function supports(ExportFormat $format): bool;

    /**
     * @param list<string>         $headers
     * @param iterable<list<mixed>> $rows
     */
    public function writeToFile(iterable $rows, array $headers, ExportContext $context, string $targetPath): void;
}
