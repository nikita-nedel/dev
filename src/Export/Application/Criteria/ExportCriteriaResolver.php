<?php

declare(strict_types=1);

namespace App\Export\Application\Criteria;

use App\Export\Application\Dto\ExportContext;
use App\Export\Application\Port\ExportCriteriaFactoryInterface;
use App\Shared\Domain\Port\QueryCriteriaInterface;
use App\Export\Domain\Model\ExportFormat;
use App\Export\Domain\Model\ExportResource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Выбирает фабрику критериев по ресурсу и собирает контекст выгрузки.
 *
 * Фабрики подключаются тегом (см. services.yaml) — добавление нового ресурса не
 * требует правок ни здесь, ни в сервисе выгрузки.
 */
final class ExportCriteriaResolver
{
    /**
     * @param iterable<ExportCriteriaFactoryInterface> $factories
     */
    public function __construct(
        private readonly iterable $factories,
    ) {
    }

    public function resolve(ExportResource $resource, Request $request): QueryCriteriaInterface
    {
        return $this->resolveFromQuery($resource, $request->query->all());
    }

    /**
     * @param array<string, mixed> $query
     */
    public function resolveFromQuery(ExportResource $resource, array $query): QueryCriteriaInterface
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
