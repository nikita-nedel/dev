<?php

declare(strict_types=1);

namespace App\Tests\Unit\Export\Application\Command\RunExport;

use App\Export\Domain\Model\ExportFile;
use App\Export\Domain\Repository\ExportFileRepositoryInterface;

final class InMemoryExportFileRepository implements ExportFileRepositoryInterface
{
    /**
     * @param list<ExportFile> $exportFiles
     */
    public function __construct(private array $exportFiles)
    {
    }

    public function add(ExportFile $exportFile): void
    {
        $this->exportFiles[] = $exportFile;
    }

    public function findByExportId(string $exportId): ?ExportFile
    {
        foreach ($this->exportFiles as $exportFile) {
            if ($exportFile->getExportId() === $exportId) {
                return $exportFile;
            }
        }

        return null;
    }

    public function findByJobIds(array $jobIds): array
    {
        return array_values(array_filter(
            $this->exportFiles,
            static fn (ExportFile $file): bool => \in_array($file->getJob()?->getJobId(), $jobIds, true),
        ));
    }
}
