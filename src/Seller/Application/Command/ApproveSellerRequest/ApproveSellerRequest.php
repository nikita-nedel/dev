<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\ApproveSellerRequest;

use App\Identity\Domain\Model\Admin;
use App\Seller\Domain\Model\SellerRequest;

/**
 * Команда «одобрить заявку на продажу».
 */
final readonly class ApproveSellerRequest
{
    public function __construct(
        public SellerRequest $request,
        public Admin $reviewedBy,
    ) {
    }
}
