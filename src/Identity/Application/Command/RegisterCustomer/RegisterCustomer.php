<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RegisterCustomer;

use App\Identity\Domain\Model\Customer;

final readonly class RegisterCustomer
{
    public function __construct(
        public Customer $customer,
        public string $plainPassword,
    ) {
    }
}
