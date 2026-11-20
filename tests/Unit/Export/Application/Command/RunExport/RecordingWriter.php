<?php

declare(strict_types=1);

namespace App\Tests\Unit\Export\Application\Command\RunExport;

use App\Export\Application\Dto\ExportContext;
use App\Export\Application\Port\ExportWriterInterface;
use App\Export\Domain\Model\ExportFormat;

final class RecordingWriter implements ExportWriterInterface
{
    public ?array $headers = null;
    public ?array $rows = null;
    public ?string $targetPath = null;

    public function supports(ExportFormat $format): bool
    {
        return true;
    }

    public function writeToFile(iterable $rows, array $headers, ExportContext $context, string $targetPath): void
    {
        $this->headers = $headers;
        $this->rows = iterator_to_array($rows);
        $this->targetPath = $targetPath;

        file_put_contents($targetPath, 'fake-xlsx');
    }
}
