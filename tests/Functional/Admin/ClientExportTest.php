<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Entity\ExportFile;
use App\Enum\Export\ExportFormat;
use App\Enum\Export\ExportResource;
use App\Message\ExportMessage;
use App\Process\Domain\Model\Job;
use App\Process\Domain\Model\JobStatus;
use App\Process\Domain\Model\JobType;
use App\Tests\Functional\FunctionalTestCase;

final class ClientExportTest extends FunctionalTestCase
{
    public function testExportRequestCreatesJobAndQueuesMessage(): void
    {
        $admin = $this->createAdmin();
        $this->client->loginUser($admin, 'admin_context');

        $this->client->request('GET', '/admin/client/export?format=xlsx');

        self::assertResponseStatusCodeSame(202);

        $payload = json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('queued', $payload['status']);

        $job = $this->em()->getRepository(Job::class)->findOneBy(['jobId' => $payload['jobId']]);
        self::assertInstanceOf(Job::class, $job);
        self::assertSame(JobStatus::Pending, $job->getStatus());
        self::assertSame(JobType::Export, $job->getType());

        $exportFile = $this->em()->getRepository(ExportFile::class)->findOneBy(['exportId' => $payload['exportId']]);
        self::assertInstanceOf(ExportFile::class, $exportFile);
        self::assertSame(ExportResource::Clients, $exportFile->getResource());
        self::assertSame(ExportFormat::Xlsx, $exportFile->getFormat());
        self::assertFalse($exportFile->isReady());
        self::assertSame($job->getId(), $exportFile->getJob()?->getId());

        $transport = static::getContainer()->get('messenger.transport.async');
        $sent = $transport->getSent();
        self::assertCount(1, $sent);
        self::assertInstanceOf(ExportMessage::class, $sent[0]->getMessage());
    }

    public function testUnknownFormatIsRejected(): void
    {
        $admin = $this->createAdmin();
        $this->client->loginUser($admin, 'admin_context');

        $this->client->request('GET', '/admin/client/export?format=csv');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDownloadOfNotReadyExportReturns404(): void
    {
        $admin = $this->createAdmin();
        $this->client->loginUser($admin, 'admin_context');

        $job = new Job(bin2hex(random_bytes(16)), JobType::Export);
        $exportFile = new ExportFile(
            exportId: bin2hex(random_bytes(16)),
            resource: ExportResource::Clients,
            format: ExportFormat::Xlsx,
            requestedBy: $admin,
            job: $job,
        );

        $em = $this->em();
        $em->persist($job);
        $em->persist($exportFile);
        $em->flush();

        $this->client->request('GET', '/admin/processes/exports/' . $exportFile->getExportId() . '/download');

        self::assertResponseStatusCodeSame(404);
    }

    public function testProcessPageRenders(): void
    {
        $admin = $this->createAdmin();
        $this->client->loginUser($admin, 'admin_context');

        $this->client->request('GET', '/admin/processes');

        self::assertResponseIsSuccessful();
    }
}
