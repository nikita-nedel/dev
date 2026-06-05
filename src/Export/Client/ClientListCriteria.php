<?php

declare(strict_types=1);

namespace App\Export\Client;

use App\Export\Contract\ExportCriteriaInterface;

final readonly class ClientListCriteria implements ExportCriteriaInterface
{
    /**
     * @param array<string, mixed> $query
     */
    public static function fromQuery(array $query): self
    {
        $search = self::nullableString($query['search'] ?? null);
        $status = self::nullableString($query['status'] ?? null);

        $period = null;
        if (isset($query['period']) && '' !== (string) $query['period']) {
            $period = filter_var($query['period'], FILTER_VALIDATE_INT);
            if (false === $period || $period < 1) {
                $period = null;
            }
        }

        $sort = self::nullableString($query['sort'] ?? null) ?? 'date-desc';

        return new self(
            search: $search,
            status: $status,
            periodDays: $period,
            sort: $sort,
        );
    }

    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?int $periodDays = null,
        public string $sort = 'date-desc',
    ) {
    }

    /**
     * @return array<string, string|int>
     */
    public function toQueryParameters(): array
    {
        $params = ['sort' => $this->sort];

        if (null !== $this->search && '' !== $this->search) {
            $params['search'] = $this->search;
        }

        if (null !== $this->status && '' !== $this->status) {
            $params['status'] = $this->status;
        }

        if (null !== $this->periodDays) {
            $params['period'] = $this->periodDays;
        }

        return $params;
    }

    private static function nullableString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return '' === $trimmed ? null : $trimmed;
    }
}