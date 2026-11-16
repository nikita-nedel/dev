<?php

declare(strict_types=1);

namespace App\Export\Presentation\Http;

use App\Identity\Domain\Model\Admin;
use App\Export\Application\Command\RequestExport\RequestExport;
use App\Export\Application\Command\RequestExport\RequestExportHandler;
use App\Identity\Domain\Query\ClientListCriteria;
use App\Export\Domain\Model\ExportFormat;
use App\Export\Domain\Model\ExportResource;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', 'app_admin_')]
final class ClientsExportController extends AbstractController
{
    #[Route('/client/export', name: 'client_export', methods: ['GET'])]
    public function __invoke(
        Request $request,
        RequestExportHandler $handler,
        #[MapQueryParameter] string $format,
    ): Response {
        $exportFormat = ExportFormat::tryFrom($format);

        if (null === $exportFormat) {
            throw new NotFoundHttpException(sprintf('Unknown export format "%s".', $format));
        }

        $user = $this->getUser();

        $exported = $handler(new RequestExport(
            resource: ExportResource::Clients,
            format: $exportFormat,
            filters: ClientListCriteria::fromQuery($request->query->all())->toQueryParameters(),
            requestedBy: $user instanceof Admin ? $user : null,
        ));

        return $this->json([
            'status' => 'queued',
            'jobId' => $exported->jobId,
            'exportId' => $exported->exportId,
            'processesUrl' => $this->generateUrl('app_admin_process_index'),
        ], Response::HTTP_ACCEPTED);
    }
}
