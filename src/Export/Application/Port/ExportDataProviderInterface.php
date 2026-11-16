<?php

declare(strict_types=1);

namespace App\Export\Application\Port;

use App\Export\Application\Dto\ExportContext;
use App\Export\Domain\Model\ExportResource;

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
