<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Job;
use App\Enum\JobStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Job>
 */
final class JobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

    public function getByJobId(string $jobId): Job
    {
        $job = $this->findOneBy(['jobId' => $jobId]);

        if (!$job instanceof Job) {
            throw new \RuntimeException(sprintf('Job "%s" was not found.', $jobId));
        }

        return $job;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function createFilteredQueryBuilder(array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('j')
            ->leftJoin('j.exportFile', 'e')
            ->leftJoin('e.requestedBy', 'u')
            ->addSelect('e', 'u')
            ->orderBy('j.createdAt', 'DESC');

        if (isset($filters['type']) && '' !== (string) $filters['type']) {
            $qb->andWhere('j.type = :type')
                ->setParameter('type', (string) $filters['type']);
        }

        if (isset($filters['status']) && '' !== (string) $filters['status']) {
            $qb->andWhere('j.status = :status')
                ->setParameter('status', (string) $filters['status']);
        }

        if (isset($filters['resource']) && '' !== (string) $filters['resource']) {
            $qb->andWhere('e.resource = :resource')
                ->setParameter('resource', (string) $filters['resource']);
        }

        if (isset($filters['format']) && '' !== (string) $filters['format']) {
            $qb->andWhere('e.format = :format')
                ->setParameter('format', (string) $filters['format']);
        }

        if (isset($filters['dateFrom']) && '' !== (string) $filters['dateFrom']) {
            $dateFrom = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $filters['dateFrom'] . ' 00:00:00');
            if ($dateFrom instanceof \DateTimeImmutable) {
                $qb->andWhere('j.createdAt >= :dateFrom')
                    ->setParameter('dateFrom', $dateFrom);
            }
        }

        if (isset($filters['dateTo']) && '' !== (string) $filters['dateTo']) {
            $dateTo = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $filters['dateTo'] . ' 23:59:59');
            if ($dateTo instanceof \DateTimeImmutable) {
                $qb->andWhere('j.createdAt <= :dateTo')
                    ->setParameter('dateTo', $dateTo);
            }
        }

        return $qb;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{total: int, pending: int, processing: int, completed: int, failed: int}
     */
    public function countStats(array $filters = []): array
    {
        return [
            'total' => $this->countForStatus($filters, null),
            'pending' => $this->countForStatus($filters, JobStatus::Pending),
            'processing' => $this->countForStatus($filters, JobStatus::Processing),
            'completed' => $this->countForStatus($filters, JobStatus::Completed),
            'failed' => $this->countForStatus($filters, JobStatus::Failed),
        ];
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function countForStatus(array $filters, ?JobStatus $status): int
    {
        $statusFilter = $filters['status'] ?? null;
        if (null !== $status && null !== $statusFilter && '' !== (string) $statusFilter && $status->value !== (string) $statusFilter) {
            return 0;
        }

        $countFilters = $filters;
        if (null !== $status) {
            $countFilters['status'] = $status->value;
        }

        return (int) $this->createFilteredQueryBuilder($countFilters)
            ->select('COUNT(j.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
