<?php

declare(strict_types=1);

namespace App\Controller\Admin\Clients;

use App\Enum\Export\ExportFormat;
use App\Enum\Export\ExportResource;
use App\Export\Client\ClientListCriteria;
use App\Repository\UserRepository;
use App\Resolver\Export\ExportCriteriaResolver;
use App\Service\Export\ExportService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        ExportService $exportService,
        ExportCriteriaResolver $exportCriteriaResolver,
        #[MapQueryParameter] string $format,
    ): Response  {
        $exportFormat = ExportFormat::tryFrom($format);

        if (null === $exportFormat) {
            throw new NotFoundHttpException(sprintf('Unknown export format "%s".', $format));
        }

        return $exportService->export(
            $exportCriteriaResolver->createContext(ExportResource::Clients, $exportFormat, $request),
        );
    }
}
