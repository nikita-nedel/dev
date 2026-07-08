<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\JobStatus;
use App\Enum\JobType;
use App\Repository\JobRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobRepository::class)]
#[ORM\Table(name: 'job')]
#[ORM\Index(columns: ['status'], name: 'idx_job_status')]
#[ORM\Index(columns: ['created_at'], name: 'idx_job_created_at')]
#[ORM\UniqueConstraint(name: 'uniq_job_job_id', fields: ['jobId'])]
class Job
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private string $jobId;

    #[ORM\Column(length: 64, enumType: JobType::class)]
    private JobType $type;

    #[ORM\Column(length: 32, enumType: JobStatus::class)]
    private JobStatus $status = JobStatus::Pending;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToOne(mappedBy: 'job', targetEntity: ExportFile::class)]
    private ?ExportFile $exportFile = null;

    public function __construct(string $jobId, JobType $type)
    {
        $now = new \DateTimeImmutable();

        $this->jobId = $jobId;
        $this->type = $type;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getType(): JobType
    {
        return $this->type;
    }

    public function getStatus(): JobStatus
    {
        return $this->status;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getExportFile(): ?ExportFile
    {
        return $this->exportFile;
    }

    public function markProcessing(): void
    {
        if (null === $this->startedAt) {
            $this->startedAt = new \DateTimeImmutable();
        }

        $this->status = JobStatus::Processing;
        $this->touch();
    }

    public function markCompleted(): void
    {
        $this->status = JobStatus::Completed;
        $this->errorMessage = null;
        $this->completedAt = new \DateTimeImmutable();
        $this->touch();
    }

    public function markFailed(string $errorMessage): void
    {
        $this->status = JobStatus::Failed;
        $this->errorMessage = substr($errorMessage, 0, 5000);
        $this->completedAt = new \DateTimeImmutable();
        $this->touch();
    }

    public function isCompleted(): bool
    {
        return JobStatus::Completed === $this->status;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
