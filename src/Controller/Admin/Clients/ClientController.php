<?php

declare(strict_types=1);

namespace App\Controller\Admin\Clients;

use App\Entity\ExportFile;
use App\Entity\Job;
use App\Entity\User;
use App\Enum\JobType;
use App\Enum\Export\ExportFormat;
use App\Enum\Export\ExportResource;
use App\Export\Client\ClientListCriteria;
use App\Message\ExportMessage;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        EntityManagerInterface $entityManager,
        #[MapQueryParameter] string $format,
    ): Response  {
        $exportFormat = ExportFormat::tryFrom($format);

        if (null === $exportFormat) {
            throw new NotFoundHttpException(sprintf('Unknown export format "%s".', $format));
        }

        $jobId = bin2hex(random_bytes(16));
        $exportId = bin2hex(random_bytes(16));
        $requestedBy = $this->getUser();
        $filters = ClientListCriteria::fromQuery($request->query->all())->toQueryParameters();

        $job = new Job($jobId, JobType::Export);
        $exportFile = new ExportFile(
            exportId: $exportId,
            resource: ExportResource::Clients,
            format: $exportFormat,
            filters: $filters,
            requestedBy: $requestedBy instanceof User ? $requestedBy : null,
            job: $job,
        );

        $entityManager->persist($job);
        $entityManager->persist($exportFile);
        $entityManager->flush();

        $messageBus->dispatch(new ExportMessage($exportId));

        return $this->json([
            'status' => 'queued',
            'jobId' => $jobId,
            'exportId' => $exportId,
            'processesUrl' => $this->generateUrl('app_admin_process_index'),
        ], Response::HTTP_ACCEPTED);
    }
}
