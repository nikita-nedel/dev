<?php

declare(strict_types=1);

namespace App\Export\Domain\Repository;

use App\Export\Domain\Model\ExportFile;

interface ExportFileRepositoryInterface
{
    public function add(ExportFile $exportFile): void;

    public function findByExportId(string $exportId): ?ExportFile;

    /**
     * @param list<string> $jobIds
     *
     * @return list<ExportFile>
     */
    public function findByJobIds(array $jobIds): array;
}
