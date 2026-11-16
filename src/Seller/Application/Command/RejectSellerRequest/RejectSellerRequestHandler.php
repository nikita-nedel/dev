<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\RejectSellerRequest;

use App\Seller\Domain\Model\SellerRequestStatus;
use App\Shared\Domain\Port\ClockInterface;
use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class RejectSellerRequestHandler
{
    public function __construct(
        private ClockInterface $clock,
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(RejectSellerRequest $command): void
    {
        $request = $command->request;

        if (SellerRequestStatus::Pending !== $request->getStatus()) {
            throw new \RuntimeException('Заявка уже обработана.');
        }

        $now = $this->clock->now();

        $this->transactions->transactional(function () use ($request, $command, $now): void {
            $request
                ->setStatus(SellerRequestStatus::Rejected)
                ->setReviewedBy($command->reviewedBy)
                ->setReviewedAt($now)
                ->setRejectionReason($command->reason);
        });
    }
}
