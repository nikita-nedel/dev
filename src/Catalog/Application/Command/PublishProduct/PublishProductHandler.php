<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\PublishProduct;

use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class PublishProductHandler
{
    public function __construct(
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(PublishProduct $command): void
    {
        $this->transactions->transactional(static function () use ($command): void {
            $command->product->publish();
        });
    }
}
