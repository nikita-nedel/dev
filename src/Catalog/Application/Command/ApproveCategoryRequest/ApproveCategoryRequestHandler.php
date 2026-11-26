<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\ApproveCategoryRequest;

use App\Catalog\Application\Service\CategoryCreator;
use App\Catalog\Domain\Model\Category;
use App\Catalog\Domain\Model\CategoryRequestStatus;
use App\Shared\Domain\Port\ClockInterface;
use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class ApproveCategoryRequestHandler
{
    public function __construct(
        private CategoryCreator $categoryCreator,
        private ClockInterface $clock,
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(ApproveCategoryRequest $command): Category
    {
        if (CategoryRequestStatus::Pending !== $command->request->getStatus()) {
            throw new \RuntimeException('Заявка уже обработана.');
        }

        return $this->transactions->transactional(function () use ($command): Category {
            $category = $this->categoryCreator->create(
                parent: $command->request->getParent(),
                name: $command->request->getName(),
            );

            $command->request->approve($command->reviewedBy, $category, $this->clock->now());

            return $category;
        });
    }
}
