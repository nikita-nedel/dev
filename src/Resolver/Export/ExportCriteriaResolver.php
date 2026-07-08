<?php

declare(strict_types=1);

namespace App\Resolver\Export;

use App\Enum\Export\ExportFormat;
use App\Enum\Export\ExportResource;
use App\Export\Contract\ExportCriteriaFactoryInterface;
use App\Export\Contract\ExportCriteriaInterface;
use App\Export\ExportContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ExportCriteriaResolver
{
    /**
     * @param iterable<ExportCriteriaFactoryInterface> $factories
     */
    public function __construct(
        private readonly iterable $factories,
    ) {
    }

    public function resolve(ExportResource $resource, Request $request): ExportCriteriaInterface
    {
        return $this->resolveFromQuery($resource, $request->query->all());
    }

    /**
     * @param array<string, mixed> $query
     */
    public function resolveFromQuery(ExportResource $resource, array $query): ExportCriteriaInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($resource)) {
                return $factory->createFromQuery($query);
            }
        }

        throw new NotFoundHttpException(sprintf(
            'Export criteria factory for "%s" is not configured.',
            $resource->value,
        ));
    }

    public function createContext(
        ExportResource $resource,
        ExportFormat $format,
        Request $request,
    ): ExportContext {
        return new ExportContext(
            resource: $resource,
            format: $format,
            criteria: $this->resolve($resource, $request),
        );
    }
}
