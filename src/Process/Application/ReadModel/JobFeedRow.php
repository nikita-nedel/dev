<?php

declare(strict_types=1);

namespace App\Process\Application\ReadModel;

use App\Export\Domain\Model\ExportFile;
use App\Export\Domain\Model\ExportResource;
use App\Process\Domain\Model\Job;

final class JobFeedRow
{
    /**
     * @return array<string, mixed>
     */
    public static function fromJob(Job $job, ?ExportFile $exportFile): array
    {
        return [
            'jobId' => $job->getJobId(),
            'type' => $job->getType()->value,
            'typeLabel' => $job->getType()->getLabel(),
            'status' => $job->getStatus()->value,
            'statusLabel' => $job->getStatus()->getLabel(),
            'resource' => $exportFile?->getResource()->value,
            'resourceLabel' => $exportFile instanceof ExportFile ? self::resourceLabel($exportFile->getResource()) : '—',
            'format' => $exportFile?->getFormat()->value,
            'formatLabel' => $exportFile instanceof ExportFile ? strtoupper($exportFile->getFormat()->value) : '—',
            'requestedBy' => $exportFile?->getRequestedBy()?->getUserIdentifier() ?? 'Система',
            'storage' => $exportFile?->getStorage()?->value,
            'storageLabel' => $exportFile?->getStorage()?->getLabel() ?? '—',
            'filePath' => $exportFile?->getFilePath(),
            'detailLabel' => $exportFile instanceof ExportFile ? ($exportFile->getFilePath() ?? 'Файл ещё не создан') : null,
            'exportId' => $exportFile?->getExportId(),
            'downloadable' => $exportFile instanceof ExportFile && $exportFile->isReady(),
            'createdAt' => self::formatDateTime($job->getCreatedAt()),
            'startedAt' => self::formatDateTime($job->getStartedAt()),
            'completedAt' => self::formatDateTime($job->getCompletedAt()),
            'errorMessage' => $job->getErrorMessage(),
        ];
    }

    private static function formatDateTime(?\DateTimeImmutable $dateTime): string
    {
        return null === $dateTime ? '—' : $dateTime->format('d.m.Y H:i');
    }

    private static function resourceLabel(ExportResource $resource): string
    {
        return match ($resource) {
            ExportResource::Clients => 'Клиенты',
        };
    }
}
