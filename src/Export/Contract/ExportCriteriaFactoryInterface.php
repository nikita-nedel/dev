<?php

declare(strict_types=1);

namespace App\Export\Contract;

use App\Enum\Export\ExportResource;
use Symfony\Component\HttpFoundation\Request;

interface ExportCriteriaFactoryInterface
{
    public function supports(ExportResource $resource): bool;

    /**
     * @param array<string, mixed> $query
     */
    public function createFromQuery(array $query): ExportCriteriaInterface;

    public function createFromRequest(Request $request): ExportCriteriaInterface;
}
