<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\ExportFile;
use App\Enum\Export\ExportFormat;
use App\Enum\Export\ExportStorage;
use App\Export\ExportContext;
use App\Message\ExportMessage;
use App\Repository\ExportFileRepository;
use App\Resolver\Export\ExportCriteriaResolver;
use App\Service\Export\ExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExportHandler
{
    public function __construct(
        private ExportFileRepository $exportFileRepository,
        private EntityManagerInterface $entityManager,
        private ExportCriteriaResolver $exportCriteriaResolver,
        private ExportService $exportService,
        private string $projectDir,
    ) {
    }

    public function __invoke(ExportMessage $message): void
    {
        $exportFile = $this->exportFileRepository->getByExportId($message->exportId);
        $job = $exportFile->getJob();

        if ($exportFile->isReady()) {
            return;
        }

        if (null === $job) {
            throw new \RuntimeException(sprintf('Export file "%s" is not linked to a background job.', $exportFile->getExportId()));
        }

        $job->markProcessing();
        $this->entityManager->flush();

        try {
            $resource = $exportFile->getResource();
            $format = $exportFile->getFormat();
            $targetPath = $this->buildExportPath($exportFile, $format);
            $context = new ExportContext(
                resource: $resource,
                format: $format,
                criteria: $this->exportCriteriaResolver->resolveFromQuery($resource, $exportFile->getFilters()),
            );

            $this->exportService->exportToFile($context, $targetPath);

            $exportFile->markStored(ExportStorage::Local, $this->buildExportRelativePath($exportFile, $format));
            $job->markCompleted();
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            $job->markFailed($exception->getMessage());
            $this->entityManager->flush();

            throw $exception;
        }
    }

    private function buildExportPath(ExportFile $exportFile, ExportFormat $format): string
    {
        return $this->projectDir . '/' . $this->buildExportRelativePath($exportFile, $format);
    }

    private function buildExportRelativePath(ExportFile $exportFile, ExportFormat $format): string
    {
        $directory = $this->projectDir . '/var/exports';

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Cannot create export directory "%s".', $directory));
        }

        return sprintf('var/exports/export_%s.%s', $exportFile->getExportId(), $format->getFileExtension());
    }
}
