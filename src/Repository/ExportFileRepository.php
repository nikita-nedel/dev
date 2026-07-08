<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExportFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExportFile>
 */
final class ExportFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExportFile::class);
    }

    public function getByExportId(string $exportId): ExportFile
    {
        $exportFile = $this->findOneBy(['exportId' => $exportId]);

        if (!$exportFile instanceof ExportFile) {
            throw new \RuntimeException(sprintf('Export file "%s" was not found.', $exportId));
        }

        return $exportFile;
    }
}
