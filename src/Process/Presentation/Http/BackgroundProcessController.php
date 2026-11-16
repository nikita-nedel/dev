<?php

declare(strict_types=1);

namespace App\Process\Presentation\Http;

use App\Export\Domain\Model\ExportFile;
use App\Export\Domain\Model\ExportFormat;
use App\Export\Domain\Model\ExportResource;
use App\Export\Domain\Repository\ExportFileRepositoryInterface;
use App\Process\Application\ReadModel\JobFeedRow;
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

#[Route('/admin/processes', 'app_admin_process_')]
final class BackgroundProcessController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        JobRepositoryInterface $jobs,
        ExportFileRepositoryInterface $exportFiles,
        PaginatorInterface $paginator,
    ): Response {
        $filters = $this->extractFilters($request);
        $pagination = $this->paginate($paginator, $jobs, $filters, $request);

        return $this->render('admin/processes/index.html.twig', [
            'admin_menu' => 'processes',
            'pagination' => $pagination,
            'export_files' => $this->exportFilesByJobId($pagination, $exportFiles),
            'stats' => $jobs->countStats($filters),
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
        JobRepositoryInterface $jobs,
        ExportFileRepositoryInterface $exportFiles,
        PaginatorInterface $paginator,
    ): JsonResponse {
        $filters = $this->extractFilters($request);
        $pagination = $this->paginate($paginator, $jobs, $filters, $request);
        $exportFilesByJobId = $this->exportFilesByJobId($pagination, $exportFiles);

        $items = [];
        foreach ($pagination as $job) {
            if (!$job instanceof Job) {
                continue;
            }

            $row = JobFeedRow::fromJob($job, $exportFilesByJobId[$job->getJobId()] ?? null);
            $row['downloadUrl'] = $row['downloadable'] && null !== $row['exportId']
                ? $this->generateUrl('app_admin_process_export_download', ['exportId' => $row['exportId']])
                : null;
            unset($row['downloadable']);

            $items[] = $row;
        }

        return $this->json([
            'items' => $items,
            'stats' => $jobs->countStats($filters),
            'total' => $pagination->getTotalItemCount(),
        ]);
    }

    /**
     * @param array<string, string> $filters
     */
    private function paginate(
        PaginatorInterface $paginator,
        JobRepositoryInterface $jobs,
        array $filters,
        Request $request,
    ): \Knp\Component\Pager\Pagination\PaginationInterface {
        return $paginator->paginate(
            $jobs->createFilteredQueryBuilder($filters),
            $request->query->getInt('page', 1),
            $request->query->getInt('perPage', 20),
        );
    }

    /**
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
}
