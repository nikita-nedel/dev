<?php

declare(strict_types=1);

namespace App\DataProvider\Export;

use App\Enum\Export\ExportResource;
use App\Export\Client\ClientListCriteria;
use App\Export\Contract\ExportDataProviderInterface;
use App\Export\ExportContext;
use App\Repository\UserRepository;

final class ClientExportDataProvider implements ExportDataProviderInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
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

        foreach ($this->userRepository->iterateClients($criteria) as $user) {
            yield [
                $user->getId(),
                $user->getFullName(),
                $user->getEmail(),
                $user->getPhone(),
                $user->getCreatedAt()?->format('d.m.Y H:i'),
            ];
        }
    }
}
