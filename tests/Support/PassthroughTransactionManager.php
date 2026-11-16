<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Shared\Domain\Port\TransactionManagerInterface;

/**
 * Реализация порта транзакций для юнит-тестов: просто выполняет замыкание.
 *
 * Так тестируется логика хендлера (изменения сущностей) без БД: в реальном
 * адаптере это wrapInTransaction с flush и commit.
 */
final class PassthroughTransactionManager implements TransactionManagerInterface
{
    public function transactional(callable $operation): mixed
    {
        return $operation();
    }
}
