<?php

declare(strict_types=1);

namespace App\DataProvider\Export;

use App\Enum\Export\ExportResource;
use App\Export\Client\ClientListCriteria;
use App\Export\Contract\ExportDataProviderInterface;
use App\Export\ExportContext;
use App\Repository\CustomerRepository;

final class ClientExportDataProvider implements ExportDataProviderInterface
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
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
