<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\ArchiveProduct;

use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class ArchiveProductHandler
{
    public function __construct(
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(ArchiveProduct $command): void
    {
        $this->transactions->transactional(static function () use ($command): void {
            $command->product->archive();
        });
    }
}
