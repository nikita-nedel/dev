<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Model\Customer;
use App\Identity\Domain\Query\ClientListCriteria;
use Doctrine\ORM\QueryBuilder;

interface CustomerRepositoryInterface
{
    public function add(Customer $customer): void;

    public function createClientsQueryBuilder(?ClientListCriteria $criteria = null): QueryBuilder;

    /**
     * @return iterable<Customer>
     */
    public function iterateClients(ClientListCriteria $criteria): iterable;
}
