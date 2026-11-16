<?php

declare(strict_types=1);

namespace App\Seller\Application\Command\ApplyForSeller;

use App\Identity\Domain\Model\Customer;
use App\Seller\Domain\Model\SellerRequest;

final readonly class ApplyForSeller
{
    public function __construct(
        public Customer $customer,
        public SellerRequest $request,
    ) {
    }
}
