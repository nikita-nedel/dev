<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Export\ExportFormat;
use App\Enum\Export\ExportResource;
use App\Enum\Export\ExportStorage;
use App\Repository\ExportFileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExportFileRepository::class)]
#[ORM\Table(name: 'export_file')]
#[ORM\UniqueConstraint(name: 'uniq_export_file_export_id', fields: ['exportId'])]
class ExportFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private string $exportId;

    #[ORM\OneToOne(inversedBy: 'exportFile', targetEntity: Job::class)]
    #[ORM\JoinColumn(name: 'job_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Job $job = null;

    #[ORM\Column(length: 64, enumType: ExportResource::class)]
    private ExportResource $resource;

    #[ORM\Column(length: 16, enumType: ExportFormat::class)]
    private ExportFormat $format;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $filters = [];

    #[ORM\ManyToOne(targetEntity: Admin::class)]
    #[ORM\JoinColumn(name: 'requested_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Admin $requestedBy = null;

    #[ORM\Column(length: 32, nullable: true, enumType: ExportStorage::class)]
    private ?ExportStorage $storage = null;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        string $exportId,
        ExportResource $resource,
        ExportFormat $format,
        array $filters = [],
        ?Admin $requestedBy = null,
        ?Job $job = null,
        ExportStorage $storage = ExportStorage::Local,
    ) {
        $now = new \DateTimeImmutable();

        $this->exportId = $exportId;
        $this->resource = $resource;
        $this->format = $format;
        $this->filters = $filters;
        $this->requestedBy = $requestedBy;
        $this->job = $job;
        $this->storage = $storage;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExportId(): string
    {
        return $this->exportId;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function getResource(): ExportResource
    {
        return $this->resource;
    }

    public function getFormat(): ExportFormat
    {
        return $this->format;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getRequestedBy(): ?Admin
    {
        return $this->requestedBy;
    }

    public function getStorage(): ?ExportStorage
    {
        return $this->storage;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function markStored(ExportStorage $storage, string $filePath): void
    {
        $this->storage = $storage;
        $this->filePath = $filePath;
        $this->completedAt = new \DateTimeImmutable();
        $this->touch();
    }

    public function isReady(): bool
    {
        return null !== $this->filePath;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
