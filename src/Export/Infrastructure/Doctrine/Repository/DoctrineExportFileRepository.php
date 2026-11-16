<?php

declare(strict_types=1);

namespace App\Export\Infrastructure\Doctrine\Repository;

use App\Export\Domain\Model\ExportFile;
use App\Export\Domain\Repository\ExportFileRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExportFile>
 */
final class DoctrineExportFileRepository extends ServiceEntityRepository implements ExportFileRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExportFile::class);
    }

    public function add(ExportFile $exportFile): void
    {
        $this->getEntityManager()->persist($exportFile);
    }

    public function findByExportId(string $exportId): ?ExportFile
    {
        return $this->findOneBy(['exportId' => $exportId]);
    }

    public function findByJobIds(array $jobIds): array
    {
        if ([] === $jobIds) {
            return [];
        }

        return $this->createQueryBuilder('e')
            ->join('e.job', 'j')
            ->andWhere('j.jobId IN (:jobIds)')
            ->setParameter('jobIds', $jobIds)
            ->getQuery()
            ->getResult();
    }
}
