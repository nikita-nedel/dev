<?php

declare(strict_types=1);

namespace App\Controller\Admin\Clients;

use App\Enum\Export\ExportFormat;
use App\Enum\Export\ExportResource;
use App\Export\Client\ClientListCriteria;
use App\Message\ClientExportMessage;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', 'app_admin_')]
class ClientController extends AbstractController
{
    #[Route('/client', name: 'client_index', methods: ['GET'])]
    public function index(
        Request $request,
        UserRepository $userRepository,
        PaginatorInterface $paginator,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $perPage = 10,
    ): Response {
        $criteria = ClientListCriteria::fromQuery($request->query->all());

        $pagination = $paginator->paginate(
            $userRepository->createClientsQueryBuilder($criteria),
            $page,
            $perPage,
        );

        $stats = [
            'total' => $pagination->getTotalItemCount(),
            'active' => 1,
            'new' => 1,
            'blocked' => 0,
        ];

        return $this->render('admin/clients/index.html.twig', [
            'admin_menu' => 'clients',
            'pagination' => $pagination,
            'stats' => $stats,
            'filter_query' => $criteria->toQueryParameters(),
        ]);
    }

    #[Route('/client/export', name: 'client_export', methods: ['GET'])]
    public function export(
        Request $request,
        MessageBusInterface $messageBus,
        #[MapQueryParameter] string $format,
    ): Response  {
        $exportFormat = ExportFormat::tryFrom($format);

        if (null === $exportFormat) {
            throw new NotFoundHttpException(sprintf('Unknown export format "%s".', $format));
        }

        $jobId = bin2hex(random_bytes(16));

        $messageBus->dispatch(new ClientExportMessage(
            jobId: $jobId,
            resource: ExportResource::Clients,
            format: $exportFormat,
            filters: ClientListCriteria::fromQuery($request->query->all())->toQueryParameters(),
        ));

        return $this->json([
            'status' => 'queued',
            'jobId' => $jobId,
            'downloadUrl' => $this->generateUrl('app_admin_client_export_download', [
                'jobId' => $jobId,
                'format' => $exportFormat->value,
            ]),
        ], Response::HTTP_ACCEPTED);
    }

    #[Route('/client/export/{jobId}/download', name: 'client_export_download', methods: ['GET'])]
    public function downloadExport(
        string $jobId,
        #[MapQueryParameter] string $format = 'xlsx',
    ): Response
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $jobId)) {
            throw $this->createNotFoundException();
        }

        $exportFormat = ExportFormat::tryFrom($format);

        if (null === $exportFormat) {
            throw new NotFoundHttpException(sprintf('Unknown export format "%s".', $format));
        }

        $path = sprintf(
            '%s/var/exports/export_%s.%s',
            $this->getParameter('kernel.project_dir'),
            $jobId,
            $exportFormat->getFileExtension(),
        );

        if (!is_file($path)) {
            return $this->json(['status' => 'processing'], Response::HTTP_ACCEPTED);
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('clients_%s.%s', $jobId, $exportFormat->getFileExtension()),
        );
        $response->headers->set('Content-Type', $exportFormat->getMimeType());

        return $response;
    }
}
