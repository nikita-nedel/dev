<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\RejectCategoryRequest;

use App\Catalog\Domain\Model\CategoryRequestStatus;
use App\Shared\Domain\Port\ClockInterface;
use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class RejectCategoryRequestHandler
{
    public function __construct(
        private ClockInterface $clock,
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(RejectCategoryRequest $command): void
    {
        if (CategoryRequestStatus::Pending !== $command->request->getStatus()) {
            throw new \RuntimeException('Заявка уже обработана.');
        }

        $this->transactions->transactional(function () use ($command): void {
            $command->request->reject($command->reviewedBy, $command->reason, $this->clock->now());
        });
    }
}
