<?php

declare(strict_types=1);

namespace App\Export\Infrastructure\DataProvider;

use App\Identity\Domain\Model\Customer;
use App\Identity\Domain\Query\ClientListCriteria;
use App\Export\Application\Dto\ExportContext;
use App\Export\Application\Port\ExportDataProviderInterface;
use App\Export\Domain\Model\ExportResource;
use App\Identity\Domain\Repository\CustomerRepositoryInterface;

final class ClientExportDataProvider implements ExportDataProviderInterface
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
    ) {
    }

    public function supports(ExportResource $resource): bool
    {
        return ExportResource::Clients === $resource;
    }

    public function getHeaders(ExportContext $context): array
    {
        return ['ID', 'Имя', 'Email', 'Телефон', 'Дата регистрации'];
    }

    public function iterateRows(ExportContext $context): iterable
    {
        $criteria = $context->criteriaAs(ClientListCriteria::class);

        foreach ($this->customerRepository->iterateClients($criteria) as $customer) {
            \assert($customer instanceof Customer);

            yield [
                $customer->getId(),
                $customer->getFullName(),
                $customer->getEmail(),
                $customer->getPhone(),
                $customer->getCreatedAt()?->format('d.m.Y H:i'),
            ];
        }
    }
}
