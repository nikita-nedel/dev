<?php

declare(strict_types=1);

namespace App\Export\Presentation\Http;

use App\Export\Domain\Model\ExportFile;
use App\Export\Domain\Model\ExportStorage;
use App\Export\Domain\Repository\ExportFileRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/processes', name: 'app_admin_process_')]
final class ExportDownloadController extends AbstractController
{
    #[Route('/exports/{exportId}/download', name: 'export_download', methods: ['GET'])]
    public function __invoke(
        string $exportId,
        ExportFileRepositoryInterface $exportFiles,
    ): Response {
        $exportFile = $exportFiles->findByExportId($exportId);

        if (null === $exportFile || !$exportFile->isReady()) {
            throw $this->createNotFoundException('Export file is not ready yet.');
        }

        if (ExportStorage::Local !== $exportFile->getStorage()) {
            throw $this->createNotFoundException('Only local export files can be downloaded from this endpoint.');
        }

        $absolutePath = $this->getParameter('kernel.project_dir') . '/' . ltrim((string) $exportFile->getFilePath(), '/');

        if (!is_file($absolutePath)) {
            throw $this->createNotFoundException('Export file was not found.');
        }

        return $this->buildFileResponse($exportFile, $absolutePath);
    }

    private function buildFileResponse(ExportFile $exportFile, string $absolutePath): BinaryFileResponse
    {
        $response = new BinaryFileResponse($absolutePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('export_%s.%s', $exportFile->getExportId(), $exportFile->getFormat()->getFileExtension()),
        );
        $response->headers->set('Content-Type', $exportFile->getFormat()->getMimeType());

        return $response;
    }
}
