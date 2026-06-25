<?php

declare(strict_types=1);

namespace App\Service\Export;

use App\Enum\Export\ExportFormat;
use App\Enum\Export\ExportResource;
use App\Export\Contract\ExportDataProviderInterface;
use App\Export\Contract\ExportWriterInterface;
use App\Export\ExportContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ExportService
{
    /**
     * @param iterable<ExportDataProviderInterface> $dataProviders
     * @param iterable<ExportWriterInterface> $writers
     */
    public function __construct(
        private readonly iterable $dataProviders,
        private readonly iterable $writers,
    ) {
    }

    public function export(ExportContext $context): Response
    {
        $provider = $this->resolveProvider($context->resource);
        $writer = $this->resolveWriter($context->format);

        return $writer->write(
            $provider->iterateRows($context),
            $provider->getHeaders($context),
            $context,
        );
    }

    public function exportToFile(ExportContext $context, string $targetPath): void
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

        throw new NotFoundHttpException(sprintf('Export provider for "%s" is not configured.', $resource->value));
    }

    private function resolveWriter(ExportFormat $format): ExportWriterInterface
    {
        foreach ($this->writers as $writer) {
            if ($writer->supports($format)) {
                return $writer;
            }
        }

        throw new NotFoundHttpException(sprintf('Export writer for "%s" is not configured.', $format->value));
    }
}
