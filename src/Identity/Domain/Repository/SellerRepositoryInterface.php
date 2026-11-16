<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Model\Customer;
use App\Identity\Domain\Model\Seller;

interface SellerRepositoryInterface
{
    public function add(Seller $seller): void;

    public function findByCustomer(Customer $customer): ?Seller;

    public function findByActivationToken(string $token): ?Seller;
}
