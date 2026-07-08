<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ExportFile;
use App\Entity\Job;
use App\Enum\Export\ExportFormat;
use App\Enum\Export\ExportResource;
use App\Enum\Export\ExportStorage;
use App\Enum\JobStatus;
use App\Enum\JobType;
use App\Repository\ExportFileRepository;
use App\Repository\JobRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/processes', 'app_admin_process_')]
final class BackgroundProcessController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        JobRepository $jobRepository,
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
        JobRepository $jobRepository,
        PaginatorInterface $paginator,
    ): JsonResponse {
        $filters = $this->extractFilters($request);
        $pagination = $paginator->paginate(
            $jobRepository->createFilteredQueryBuilder($filters),
            $request->query->getInt('page', 1),
            $request->query->getInt('perPage', 20),
        );

        $items = [];
        foreach ($pagination as $job) {
            if ($job instanceof Job) {
                $items[] = $this->serializeJob($job);
            }
        }

        return $this->json([
            'items' => $items,
            'stats' => $jobRepository->countStats($filters),
            'total' => $pagination->getTotalItemCount(),
        ]);
    }

    #[Route('/exports/{exportId}/download', name: 'export_download', methods: ['GET'])]
    public function downloadExport(string $exportId, ExportFileRepository $exportFileRepository): Response
    {
        $exportFile = $exportFileRepository->getByExportId($exportId);
        $filePath = $exportFile->getFilePath();

        if (!$exportFile->isReady() || null === $filePath) {
            throw $this->createNotFoundException('Export file is not ready yet.');
        }

        if (ExportStorage::Local !== $exportFile->getStorage()) {
            throw $this->createNotFoundException('Only local export files can be downloaded from this endpoint.');
        }

        $absolutePath = $this->getParameter('kernel.project_dir') . '/' . ltrim($filePath, '/');
        if (!is_file($absolutePath)) {
            throw $this->createNotFoundException('Export file was not found.');
        }

        $response = new BinaryFileResponse($absolutePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('export_%s.%s', $exportFile->getExportId(), $exportFile->getFormat()->getFileExtension()),
        );
        $response->headers->set('Content-Type', $exportFile->getFormat()->getMimeType());

        return $response;
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
    private function serializeJob(Job $job): array
    {
        $exportFile = $job->getExportFile();
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
