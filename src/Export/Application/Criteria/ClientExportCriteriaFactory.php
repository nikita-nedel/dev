<?php

declare(strict_types=1);

namespace App\Export\Application\Criteria;

use App\Export\Application\Port\ExportCriteriaFactoryInterface;
use App\Export\Application\Port\ExportCriteriaInterface;
use App\Export\Domain\Model\ExportResource;
use Symfony\Component\HttpFoundation\Request;

final class ClientExportCriteriaFactory implements ExportCriteriaFactoryInterface
{
    public function supports(ExportResource $resource): bool
    {
        return ExportResource::Clients === $resource;
    }

    public function createFromRequest(Request $request): ExportCriteriaInterface
    {
        return $this->createFromQuery($request->query->all());
    }

    /**
     * @param array<string, mixed> $query
     */
    public function createFromQuery(array $query): ExportCriteriaInterface
    {
        return ClientListCriteria::fromQuery($query);
    }
}
