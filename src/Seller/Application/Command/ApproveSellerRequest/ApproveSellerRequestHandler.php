<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\ApproveSellerRequest;

use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Model\SellerRequestStatus;
use App\Shared\Domain\Port\ClockInterface;
use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class ApproveSellerRequestHandler
{
    private const ACTIVATION_TOKEN_TTL = '+7 days';

    public function __construct(
        private ClockInterface $clock,
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(ApproveSellerRequest $command): Seller
    {
        $request = $command->request;

        if (SellerRequestStatus::Pending !== $request->getStatus()) {
            throw new \RuntimeException('Заявка уже обработана.');
        }

        $seller = $request->getSeller();

        if (null === $seller) {
            throw new \RuntimeException('Продавец не найден для заявки.');
        }

        $now = $this->clock->now();

        return $this->transactions->transactional(function () use ($request, $seller, $command, $now): Seller {
            $request
                ->setStatus(SellerRequestStatus::Approved)
                ->setReviewedBy($command->reviewedBy)
                ->setReviewedAt($now)
                ->setRejectionReason(null);

            $seller
                ->setApprovedAt($now)
                ->setActivationToken(bin2hex(random_bytes(32)))
                ->setActivationTokenExpiresAt($now->modify(self::ACTIVATION_TOKEN_TTL));

            return $seller;
        });
    }
}
