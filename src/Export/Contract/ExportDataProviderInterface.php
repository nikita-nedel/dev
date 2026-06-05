<?php

declare(strict_types=1);

namespace App\Export\Contract;

use App\Enum\Export\ExportResource;
use App\Export\ExportContext;

interface ExportDataProviderInterface
{
    public function supports(ExportResource $resource): bool;

    /**
     * @return list<string>
     */
    public function getHeaders(ExportContext $context): array;

    /**
     * @return iterable<list<mixed>>
     */
    public function iterateRows(ExportContext $context): iterable;
}
