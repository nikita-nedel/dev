<?php

declare(strict_types=1);

namespace App\Seller\Domain\Repository;

use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Model\SellerRequest;
use App\Seller\Domain\Model\SellerRequestStatus;
use Doctrine\ORM\QueryBuilder;

/**
 * Порт доступа к заявкам на продажу.
 *
 * Технический долг: createListQueryBuilder() отдаёт QueryBuilder для пагинации в
 * админском списке — заменим на read-model на шаге Presentation.
 */
interface SellerRequestRepositoryInterface
{
    public function add(SellerRequest $request): void;

    public function findBySeller(Seller $seller): ?SellerRequest;

    public function createListQueryBuilder(?SellerRequestStatus $status = null): QueryBuilder;
}
