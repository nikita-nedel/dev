<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RegisterCustomer;

use App\Identity\Domain\Model\Customer;
use App\Identity\Domain\Repository\CustomerRepositoryInterface;
use App\Shared\Domain\Port\PasswordHasherInterface;
use App\Shared\Domain\Port\TransactionManagerInterface;

final readonly class RegisterCustomerHandler
{
    public function __construct(
        private CustomerRepositoryInterface $customers,
        private PasswordHasherInterface $passwordHasher,
        private TransactionManagerInterface $transactions,
    ) {
    }

    public function __invoke(RegisterCustomer $command): Customer
    {
        $customer = $command->customer
            ->setRoles(['ROLE_CUSTOMER'])
            ->setPassword($this->passwordHasher->hash($command->customer, $command->plainPassword));

        $this->transactions->transactional(fn () => $this->customers->add($customer));

        return $customer;
    }
}
