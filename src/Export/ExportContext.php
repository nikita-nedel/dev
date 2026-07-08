<?php

declare(strict_types=1);

namespace App\Export;

use App\Enum\Export\ExportFormat;
use App\Enum\Export\ExportResource;
use App\Export\Client\ClientListCriteria;
use App\Export\Contract\ExportCriteriaInterface;

final readonly class ExportContext
{
    public function __construct(
        public ExportResource $resource,
        public ExportFormat $format,
        public ExportCriteriaInterface $criteria,
    ) {
    }

    public function criteriaAs(string $class): ExportCriteriaInterface
    {
        if (!$this->criteria instanceof $class) {
            throw new \InvalidArgumentException(sprintf(
                'Expected %s for resource "%s", got %s.',
                $class,
                $this->resource->value,
                $this->criteria::class,
            ));
        }

        return $this->criteria;
    }

    public static function forClients(ExportFormat $format, ClientListCriteria $criteria): self
    {
        return new self(ExportResource::Clients, $format, $criteria);
    }
}
