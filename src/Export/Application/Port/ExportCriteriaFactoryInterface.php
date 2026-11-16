<?php

declare(strict_types=1);

namespace App\Export\Application\Port;

use App\Export\Domain\Model\ExportResource;
use App\Shared\Domain\Port\QueryCriteriaInterface;
use Symfony\Component\HttpFoundation\Request;

interface ExportCriteriaFactoryInterface
{
    public function supports(ExportResource $resource): bool;

    /**
     * @param array<string, mixed> $query
     */
    public function createFromQuery(array $query): QueryCriteriaInterface;

    public function createFromRequest(Request $request): QueryCriteriaInterface;
}
