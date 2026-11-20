<?php

declare(strict_types=1);

namespace App\Tests\Unit\Export\Application\Command\RunExport;

use App\Export\Application\Command\RunExport\RunExportHandler;
use App\Export\Application\Command\RunExport\RunExportMessage;
use App\Export\Application\Criteria\ClientExportCriteriaFactory;
use App\Export\Application\Criteria\ExportCriteriaResolver;
use App\Export\Application\Port\ExportDataProviderInterface;
use App\Export\Application\Port\ExportWriterInterface;
use App\Export\Domain\Model\ExportFile;
use App\Export\Domain\Model\ExportFormat;
use App\Export\Domain\Model\ExportResource;
use App\Export\Domain\Model\ExportStorage;
use App\Export\Domain\Repository\ExportFileRepositoryInterface;
use App\Process\Domain\Model\Job;
use App\Process\Domain\Model\JobStatus;
use App\Process\Domain\Model\JobType;
use PHPUnit\Framework\TestCase;

final class RunExportHandlerTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir() . '/export-test-' . bin2hex(random_bytes(4));
        mkdir($this->projectDir, 0775, true);
    }

    public function testWritesFileAndCompletesJob(): void
    {
        $job = new Job('job-1', JobType::Export);
        $exportFile = $this->createExportFile('export-1', $job);
        $writer = new RecordingWriter();

        $handler = $this->createHandler(
            new InMemoryExportFileRepository([$exportFile]),
            $writer,
            new FakeExportDataProvider(),
        );

        $handler(new RunExportMessage('export-1'));

        self::assertSame(JobStatus::Completed, $job->getStatus());
        self::assertTrue($exportFile->isReady());
        self::assertSame(ExportStorage::Local, $exportFile->getStorage());
        self::assertSame(['ID', 'Email'], $writer->headers);
        self::assertSame([[1, 'a@example.com'], [2, 'b@example.com']], $writer->rows);
        self::assertStringEndsWith('/var/exports/export_export-1.xlsx', $writer->targetPath);
        self::assertFileExists($writer->targetPath);
    }

    public function testUnknownExportIsIgnored(): void
    {
        $handler = $this->createHandler(new InMemoryExportFileRepository([]), new RecordingWriter(), new FakeExportDataProvider());

        $handler(new RunExportMessage('missing'));

        self::assertTrue(true, 'Сообщение на несуществующую выгрузку не должно бросать исключение');
    }

    public function testAlreadyReadyExportIsNotRegenerated(): void
    {
        $job = new Job('job-2', JobType::Export);
        $job->markCompleted();
        $exportFile = $this->createExportFile('export-2', $job);
        $exportFile->markStored(ExportStorage::Local, 'var/exports/export_export-2.xlsx');
        $writer = new RecordingWriter();

        $handler = $this->createHandler(new InMemoryExportFileRepository([$exportFile]), $writer, new FakeExportDataProvider());

        $handler(new RunExportMessage('export-2'));

        self::assertNull($writer->targetPath, 'Повторная доставка не должна писать файл заново');
        self::assertSame(JobStatus::Completed, $job->getStatus());
    }

    public function testFailureMarksJobFailedAndRethrows(): void
    {
        $job = new Job('job-3', JobType::Export);
        $exportFile = $this->createExportFile('export-3', $job);

        $handler = $this->createHandler(
            new InMemoryExportFileRepository([$exportFile]),
            new ThrowingWriter(),
            new FakeExportDataProvider(),
        );

        try {
            $handler(new RunExportMessage('export-3'));
            self::fail('Ожидалось исключение от writer-а');
        } catch (\RuntimeException $exception) {
            self::assertSame('writer exploded', $exception->getMessage());
        }

        self::assertSame(JobStatus::Failed, $job->getStatus());
        self::assertSame('writer exploded', $job->getErrorMessage());
        self::assertFalse($exportFile->isReady());
    }

    private function createHandler(
        ExportFileRepositoryInterface $exportFiles,
        ExportWriterInterface $writer,
        ExportDataProviderInterface $provider,
    ): RunExportHandler {
        return new RunExportHandler(
            exportFiles: $exportFiles,
            criteriaResolver: new ExportCriteriaResolver([new ClientExportCriteriaFactory()]),
            transactions: new FakeTransactionManager(),
            dataProviders: [$provider],
            writers: [$writer],
            projectDir: $this->projectDir,
        );
    }

    private function createExportFile(string $exportId, Job $job): ExportFile
    {
        return new ExportFile(
            exportId: $exportId,
            resource: ExportResource::Clients,
            format: ExportFormat::Xlsx,
            filters: ['sort' => 'date-desc'],
            job: $job,
        );
    }
}
