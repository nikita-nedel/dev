<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Enum\Export\ExportFormat;
use App\Export\ExportContext;
use App\Message\ClientExportMessage;
use App\Resolver\Export\ExportCriteriaResolver;
use App\Service\Export\ExportService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ClientExportHandler
{
    public function __construct(
        private ExportCriteriaResolver $exportCriteriaResolver,
        private ExportService $exportService,
        private string $projectDir,
    ) {
    }

    public function __invoke(ClientExportMessage $message): void
    {
        $path = $this->buildExportPath($message->jobId, $message->format);

        if (is_file($path)) {
            return;
        }

        $context = new ExportContext(
            resource: $message->resource,
            format: $message->format,
            criteria: $this->exportCriteriaResolver->resolveFromQuery($message->resource, $message->filters),
        );

        $this->exportService->exportToFile($context, $path);
    }

    private function buildExportPath(string $jobId, ExportFormat $format): string
    {
        $directory = $this->projectDir . '/var/exports';

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Cannot create export directory "%s".', $directory));
        }

        return sprintf('%s/export_%s.%s', $directory, $jobId, $format->getFileExtension());
    }
}
