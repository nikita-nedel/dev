<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\RejectSellerRequest;

use App\Identity\Domain\Model\Admin;
use App\Seller\Domain\Model\SellerRequest;

/**
 * Команда «отклонить заявку на продажу».
 */
final readonly class RejectSellerRequest
{
    public function __construct(
        public SellerRequest $request,
        public Admin $reviewedBy,
        public ?string $reason = null,
    ) {
    }
}
