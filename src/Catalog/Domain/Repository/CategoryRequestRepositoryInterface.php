<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Model\CategoryRequest;
use App\Catalog\Domain\Model\CategoryRequestStatus;
use App\Identity\Domain\Model\Seller;
use Doctrine\ORM\QueryBuilder;

interface CategoryRequestRepositoryInterface
{
    public function add(CategoryRequest $request): void;

    public function findById(int $id): ?CategoryRequest;

    public function createListQueryBuilder(?CategoryRequestStatus $status = null): QueryBuilder;

    /**
     * @return list<CategoryRequest>
     */
    public function findBySeller(Seller $seller): array;

    public function existsPending(Seller $seller, string $name, ?int $parentId): bool;

    public function countPendingBySeller(Seller $seller): int;

    public function countPending(): int;
}
