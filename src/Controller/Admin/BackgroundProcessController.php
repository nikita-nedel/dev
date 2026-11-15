<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Export\Domain\Model\ExportFile;
use App\Export\Domain\Model\ExportFormat;
use App\Export\Domain\Model\ExportResource;
use App\Export\Domain\Repository\ExportFileRepositoryInterface;
use App\Process\Domain\Model\Job;
use App\Process\Domain\Model\JobStatus;
use App\Process\Domain\Model\JobType;
use App\Process\Domain\Repository\JobRepositoryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Список фоновых процессов в админке (рендер + JSON-feed для автообновления).
 *
 * Контроллер сознательно читает оба контекста: Job из Process и ExportFile из Export.
 * Домен Process при этом про Export не знает — обратной связи у Job нет, поэтому
 * контроллер сам собирает карту «jobId -> ExportFile» одним запросом.
 */
#[Route('/admin/processes', 'app_admin_process_')]
final class BackgroundProcessController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        JobRepositoryInterface $jobRepository,
        ExportFileRepositoryInterface $exportFiles,
        PaginatorInterface $paginator,
    ): Response {
        $filters = $this->extractFilters($request);
        $pagination = $paginator->paginate(
            $jobRepository->createFilteredQueryBuilder($filters),
            $request->query->getInt('page', 1),
            $request->query->getInt('perPage', 20),
        );

        return $this->render('admin/processes/index.html.twig', [
            'admin_menu' => 'processes',
            'pagination' => $pagination,
            'export_files' => $this->exportFilesByJobId($pagination, $exportFiles),
            'stats' => $jobRepository->countStats($filters),
            'filters' => $filters,
            'job_types' => JobType::cases(),
            'job_statuses' => JobStatus::cases(),
            'export_resources' => ExportResource::cases(),
            'export_formats' => ExportFormat::cases(),
        ]);
    }

    #[Route('/feed', name: 'feed', methods: ['GET'])]
    public function feed(
        Request $request,
        JobRepositoryInterface $jobRepository,
        ExportFileRepositoryInterface $exportFiles,
        PaginatorInterface $paginator,
    ): JsonResponse {
        $filters = $this->extractFilters($request);
        $pagination = $paginator->paginate(
            $jobRepository->createFilteredQueryBuilder($filters),
            $request->query->getInt('page', 1),
            $request->query->getInt('perPage', 20),
        );

        $exportFilesByJobId = $this->exportFilesByJobId($pagination, $exportFiles);

        $items = [];
        foreach ($pagination as $job) {
            if ($job instanceof Job) {
                $items[] = $this->serializeJob($job, $exportFilesByJobId[$job->getJobId()] ?? null);
            }
        }

        return $this->json([
            'items' => $items,
            'stats' => $jobRepository->countStats($filters),
            'total' => $pagination->getTotalItemCount(),
        ]);
    }

    /**
     * Один запрос на страницу: собираем ExportFile для всех jobId сразу.
     *
     * @param iterable<mixed> $jobs
     *
     * @return array<string, ExportFile>
     */
    private function exportFilesByJobId(iterable $jobs, ExportFileRepositoryInterface $exportFiles): array
    {
        $jobIds = [];
        foreach ($jobs as $job) {
            if ($job instanceof Job) {
                $jobIds[] = $job->getJobId();
            }
        }

        $map = [];
        foreach ($exportFiles->findByJobIds($jobIds) as $exportFile) {
            $jobId = $exportFile->getJob()?->getJobId();

            if (null !== $jobId) {
                $map[$jobId] = $exportFile;
            }
        }

        return $map;
    }

    /**
     * @return array<string, string>
     */
    private function extractFilters(Request $request): array
    {
        $filters = [];
        foreach (['type', 'status', 'resource', 'format', 'dateFrom', 'dateTo'] as $key) {
            $value = trim((string) $request->query->get($key, ''));
            if ('' !== $value) {
                $filters[$key] = $value;
            }
        }

        return $filters;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeJob(Job $job, ?ExportFile $exportFile): array
    {
        $downloadUrl = null;

        if ($exportFile instanceof ExportFile && $exportFile->isReady()) {
            $downloadUrl = $this->generateUrl('app_admin_process_export_download', [
                'exportId' => $exportFile->getExportId(),
            ]);
        }

        return [
            'jobId' => $job->getJobId(),
            'type' => $job->getType()->value,
            'typeLabel' => $job->getType()->getLabel(),
            'status' => $job->getStatus()->value,
            'statusLabel' => $job->getStatus()->getLabel(),
            'resource' => $exportFile?->getResource()->value,
            'resourceLabel' => $exportFile instanceof ExportFile ? $this->formatResourceLabel($exportFile->getResource()) : '—',
            'format' => $exportFile?->getFormat()->value,
            'formatLabel' => $exportFile instanceof ExportFile ? strtoupper($exportFile->getFormat()->value) : '—',
            'requestedBy' => $exportFile?->getRequestedBy()?->getUserIdentifier() ?? 'Система',
            'storage' => $exportFile?->getStorage()?->value,
            'storageLabel' => $exportFile?->getStorage()?->getLabel() ?? '—',
            'filePath' => $exportFile?->getFilePath(),
            'detailLabel' => $exportFile instanceof ExportFile ? ($exportFile->getFilePath() ?? 'Файл ещё не создан') : null,
            'exportId' => $exportFile?->getExportId(),
            'createdAt' => $this->formatDateTime($job->getCreatedAt()),
            'startedAt' => $this->formatDateTime($job->getStartedAt()),
            'completedAt' => $this->formatDateTime($job->getCompletedAt()),
            'errorMessage' => $job->getErrorMessage(),
            'downloadUrl' => $downloadUrl,
        ];
    }

    private function formatDateTime(?\DateTimeImmutable $dateTime): string
    {
        return null === $dateTime ? '—' : $dateTime->format('d.m.Y H:i');
    }

    private function formatResourceLabel(ExportResource $resource): string
    {
        return match ($resource) {
            ExportResource::Clients => 'Клиенты',
        };
    }
}
