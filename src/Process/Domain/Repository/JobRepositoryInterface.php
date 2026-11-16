<?php

declare(strict_types=1);

namespace App\Process\Domain\Repository;

use App\Process\Domain\Model\Job;
use Doctrine\ORM\QueryBuilder;

interface JobRepositoryInterface
{
    public function add(Job $job): void;

    public function getByJobId(string $jobId): Job;

    /**
     * @param array<string, mixed> $filters
     */
    public function createFilteredQueryBuilder(array $filters = []): QueryBuilder;

    /**
     * @param array<string, mixed> $filters
     *
     * @return array{total: int, pending: int, processing: int, completed: int, failed: int}
     */
    public function countStats(array $filters = []): array;
}
