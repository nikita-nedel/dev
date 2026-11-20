<?php

declare(strict_types=1);

namespace App\Export\Application\Criteria;

use App\Export\Application\Port\ExportCriteriaFactoryInterface;
use App\Export\Domain\Model\ExportResource;
use App\Identity\Domain\Query\ClientListCriteria;
use App\Shared\Domain\Port\QueryCriteriaInterface;
use Symfony\Component\HttpFoundation\Request;

final class ClientExportCriteriaFactory implements ExportCriteriaFactoryInterface
{
    public function supports(ExportResource $resource): bool
    {
        return ExportResource::Clients === $resource;
    }

    public function createFromRequest(Request $request): QueryCriteriaInterface
    {
        return $this->createFromQuery($request->query->all());
    }

    /**
     * @param array<string, mixed> $query
     */
    public function createFromQuery(array $query): QueryCriteriaInterface
    {
        return ClientListCriteria::fromQuery($query);
    }
}
