<?php

declare(strict_types=1);

namespace App\Export\Application\Dto;

use App\Export\Domain\Model\ExportFormat;
use App\Export\Domain\Model\ExportResource;
use App\Shared\Domain\Port\QueryCriteriaInterface;

final readonly class ExportContext
{
    public function __construct(
        public ExportResource $resource,
        public ExportFormat $format,
        public QueryCriteriaInterface $criteria,
    ) {
    }

    public function criteriaAs(string $class): QueryCriteriaInterface
    {
        if (!$this->criteria instanceof $class) {
            throw new \InvalidArgumentException(sprintf('Expected %s for resource "%s", got %s.', $class, $this->resource->value, $this->criteria::class));
        }

        return $this->criteria;
    }

    public static function forClients(ExportFormat $format, QueryCriteriaInterface $criteria): self
    {
        return new self(ExportResource::Clients, $format, $criteria);
    }
}
