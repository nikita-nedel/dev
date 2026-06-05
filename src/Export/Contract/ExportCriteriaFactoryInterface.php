<?php

declare(strict_types=1);

namespace App\Export\Contract;

use App\Enum\Export\ExportResource;
use Symfony\Component\HttpFoundation\Request;

interface ExportCriteriaFactoryInterface
{
    public function supports(ExportResource $resource): bool;

    public function createFromRequest(Request $request): ExportCriteriaInterface;
}
