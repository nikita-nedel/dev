<?php

declare(strict_types=1);

namespace App\Tests\Unit\Export\Application\Command\RunExport;

use App\Shared\Domain\Port\TransactionManagerInterface;

final class FakeTransactionManager implements TransactionManagerInterface
{
    public function transactional(callable $operation): mixed
    {
        return $operation();
    }
}
