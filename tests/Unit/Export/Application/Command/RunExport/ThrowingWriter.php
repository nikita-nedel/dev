<?php

declare(strict_types=1);

namespace App\Tests\Unit\Export\Application\Command\RunExport;

use App\Export\Application\Dto\ExportContext;
use App\Export\Application\Port\ExportWriterInterface;
use App\Export\Domain\Model\ExportFormat;

final class ThrowingWriter implements ExportWriterInterface
{
    public function supports(ExportFormat $format): bool
    {
        return true;
    }

    public function writeToFile(iterable $rows, array $headers, ExportContext $context, string $targetPath): void
    {
        throw new \RuntimeException('writer exploded');
    }
}
