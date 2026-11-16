<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Shared\Domain\Port\TransactionManagerInterface;

final class PassthroughTransactionManager implements TransactionManagerInterface
{
    public function transactional(callable $operation): mixed
    {
        return $operation();
    }
}
