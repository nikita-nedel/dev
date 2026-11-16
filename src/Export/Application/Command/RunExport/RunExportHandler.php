<?php

declare(strict_types=1);

namespace App\Export\Application\Command\RunExport;

use App\Export\Application\Criteria\ExportCriteriaResolver;
use App\Export\Application\Dto\ExportContext;
use App\Export\Application\Port\ExportDataProviderInterface;
use App\Export\Application\Port\ExportWriterInterface;
use App\Export\Domain\Model\ExportFile;
use App\Export\Domain\Model\ExportFormat;
use App\Export\Domain\Model\ExportResource;
use App\Export\Domain\Model\ExportStorage;
use App\Export\Domain\Repository\ExportFileRepositoryInterface;
use App\Shared\Domain\Port\TransactionManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RunExportHandler
{
    /**
     * @param iterable<ExportDataProviderInterface> $dataProviders
     * @param iterable<ExportWriterInterface>       $writers
     */
    public function __construct(
        private readonly ExportFileRepositoryInterface $exportFiles,
        private readonly ExportCriteriaResolver $criteriaResolver,
        private readonly TransactionManagerInterface $transactions,
        private readonly iterable $dataProviders,
        private readonly iterable $writers,
        private readonly string $projectDir,
    ) {
    }

    public function __invoke(RunExportMessage $message): void
    {
        $exportFile = $this->exportFiles->findByExportId($message->exportId);

        if (null === $exportFile) {
            return;
        }

        if ($exportFile->isReady()) {
            return;
        }

        $job = $exportFile->getJob();

        if (null === $job) {
            throw new \RuntimeException(sprintf('Export file "%s" is not linked to a background job.', $exportFile->getExportId()));
        }

        $this->transactions->transactional(static fn () => $job->markProcessing());

        try {
            $format = $exportFile->getFormat();
            $context = new ExportContext(
                resource: $exportFile->getResource(),
                format: $format,
                criteria: $this->criteriaResolver->resolveFromQuery($exportFile->getResource(), $exportFile->getFilters()),
            );

            $this->writeFile($context, $this->buildAbsolutePath($exportFile, $format));

            $this->transactions->transactional(function () use ($exportFile, $job, $format): void {
                $exportFile->markStored(ExportStorage::Local, $this->buildRelativePath($exportFile, $format));
                $job->markCompleted();
            });
        } catch (\Throwable $exception) {
            $this->transactions->transactional(static fn () => $job->markFailed($exception->getMessage()));

            throw $exception;
        }
    }

    private function writeFile(ExportContext $context, string $targetPath): void
    {
        $provider = $this->resolveProvider($context->resource);
        $writer = $this->resolveWriter($context->format);

        $writer->writeToFile(
            $provider->iterateRows($context),
            $provider->getHeaders($context),
            $context,
            $targetPath,
        );
    }

    private function resolveProvider(ExportResource $resource): ExportDataProviderInterface
    {
        foreach ($this->dataProviders as $provider) {
            if ($provider->supports($resource)) {
                return $provider;
            }
        }

        throw new \RuntimeException(sprintf('Export provider for "%s" is not configured.', $resource->value));
    }

    private function resolveWriter(ExportFormat $format): ExportWriterInterface
    {
        foreach ($this->writers as $writer) {
            if ($writer->supports($format)) {
                return $writer;
            }
        }

        throw new \RuntimeException(sprintf('Export writer for "%s" is not configured.', $format->value));
    }

    private function buildAbsolutePath(ExportFile $exportFile, ExportFormat $format): string
    {
        return $this->projectDir . '/' . $this->buildRelativePath($exportFile, $format);
    }

    private function buildRelativePath(ExportFile $exportFile, ExportFormat $format): string
    {
        $directory = $this->projectDir . '/var/exports';

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Cannot create export directory "%s".', $directory));
        }

        return sprintf('var/exports/export_%s.%s', $exportFile->getExportId(), $format->getFileExtension());
    }
}
