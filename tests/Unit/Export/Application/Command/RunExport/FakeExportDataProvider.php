<?php

declare(strict_types=1);

namespace App\Tests\Unit\Export\Application\Command\RunExport;

use App\Export\Application\Dto\ExportContext;
use App\Export\Application\Port\ExportDataProviderInterface;
use App\Export\Domain\Model\ExportResource;
use App\Identity\Domain\Query\ClientListCriteria;

final class FakeExportDataProvider implements ExportDataProviderInterface
{
    public function supports(ExportResource $resource): bool
    {
        return true;
    }

    public function getHeaders(ExportContext $context): array
    {
        return ['ID', 'Email'];
    }

    public function iterateRows(ExportContext $context): iterable
    {
        $context->criteriaAs(ClientListCriteria::class);

        yield [1, 'a@example.com'];
        yield [2, 'b@example.com'];
    }
}
