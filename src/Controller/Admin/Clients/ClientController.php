<?php

declare(strict_types=1);

namespace App\Controller\Admin\Clients;

use App\Identity\Domain\Query\ClientListCriteria;
use App\Identity\Domain\Repository\CustomerRepositoryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Список клиентов в админке.
 *
 * Здесь осталась только страница списка: постановка выгрузки переехала в
 * App\Export\Presentation\Http\ClientsExportController (тот же URL).
 */
#[Route('/admin', 'app_admin_')]
class ClientController extends AbstractController
{
    #[Route('/client', name: 'client_index', methods: ['GET'])]
    public function index(
        Request $request,
        CustomerRepositoryInterface $customerRepository,
        PaginatorInterface $paginator,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $perPage = 10,
    ): Response {
        $criteria = ClientListCriteria::fromQuery($request->query->all());

        $pagination = $paginator->paginate(
            $customerRepository->createClientsQueryBuilder($criteria),
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
}
