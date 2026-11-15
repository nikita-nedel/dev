<?php

declare(strict_types=1);

namespace App\Export\Application\Command\RequestExport;

use App\Export\Application\Command\RunExport\RunExportMessage;
use App\Export\Application\Dto\ExportRequested;
use App\Export\Domain\Model\ExportFile;
use App\Export\Domain\Repository\ExportFileRepositoryInterface;
use App\Process\Domain\Model\Job;
use App\Process\Domain\Model\JobType;
use App\Process\Domain\Repository\JobRepositoryInterface;
use App\Shared\Domain\Port\TransactionManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class RequestExportHandler
{
    public function __construct(
        private JobRepositoryInterface $jobs,
        private ExportFileRepositoryInterface $exportFiles,
        private TransactionManagerInterface $transactions,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(RequestExport $command): ExportRequested
    {
        $jobId = bin2hex(random_bytes(16));
        $exportId = bin2hex(random_bytes(16));

        $this->transactions->transactional(function () use ($command, $jobId, $exportId): void {
            $job = new Job($jobId, JobType::Export);

            $this->jobs->add($job);

            $this->exportFiles->add(new ExportFile(
                exportId: $exportId,
                resource: $command->resource,
                format: $command->format,
                filters: $command->filters,
                requestedBy: $command->requestedBy,
                job: $job,
            ));
        });

        $this->bus->dispatch(new RunExportMessage($exportId));

        return new ExportRequested($jobId, $exportId);
    }
}
