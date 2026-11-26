<?php

declare(strict_types=1);

namespace App\Tests\Unit\Catalog\Application\Command;

use App\Catalog\Domain\Model\CategoryRequest;
use App\Catalog\Domain\Model\CategoryRequestStatus;
use App\Catalog\Domain\Repository\CategoryRequestRepositoryInterface;
use App\Identity\Domain\Model\Seller;
use Doctrine\ORM\QueryBuilder;

final class InMemoryCategoryRequestRepository implements CategoryRequestRepositoryInterface
{
    /**
     * @var list<CategoryRequest>
     */
    public array $requests = [];

    public function add(CategoryRequest $request): void
    {
        $this->requests[] = $request;
    }

    public function findById(int $id): ?CategoryRequest
    {
        foreach ($this->requests as $request) {
            if ($request->getId() === $id) {
                return $request;
            }
        }

        return null;
    }

    public function createListQueryBuilder(?CategoryRequestStatus $status = null): QueryBuilder
    {
        throw new \LogicException('Не используется в юнит-тестах.');
    }

    public function findBySeller(Seller $seller): array
    {
        return array_values(array_filter(
            $this->requests,
            static fn (CategoryRequest $request): bool => $request->getSeller() === $seller,
        ));
    }

    public function existsPending(Seller $seller, string $name, ?int $parentId): bool
    {
        foreach ($this->requests as $request) {
            if ($request->getSeller() !== $seller || CategoryRequestStatus::Pending !== $request->getStatus()) {
                continue;
            }

            if (mb_strtolower($request->getName()) !== mb_strtolower($name)) {
                continue;
            }

            if (null === $parentId ? null === $request->getParent() : $request->getParent()?->getId() === $parentId) {
                return true;
            }
        }

        return false;
    }

    public function countPendingBySeller(Seller $seller): int
    {
        return count(array_filter(
            $this->requests,
            static fn (CategoryRequest $request): bool => $request->getSeller() === $seller
                && CategoryRequestStatus::Pending === $request->getStatus(),
        ));
    }

    public function countPending(): int
    {
        return count(array_filter(
            $this->requests,
            static fn (CategoryRequest $request): bool => CategoryRequestStatus::Pending === $request->getStatus(),
        ));
    }
}
