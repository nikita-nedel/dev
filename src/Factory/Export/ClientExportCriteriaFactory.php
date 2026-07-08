<?php

declare(strict_types=1);

namespace App\Factory\Export;

use App\Enum\Export\ExportResource;
use App\Export\Client\ClientListCriteria;
use App\Export\Contract\ExportCriteriaFactoryInterface;
use App\Export\Contract\ExportCriteriaInterface;
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
