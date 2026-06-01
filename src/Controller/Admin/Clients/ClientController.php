<?php

declare(strict_types=1);

namespace App\Controller\Admin\Clients;

use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', 'app_admin_')]
class ClientController extends AbstractController
{
    #[Route('/client', name: 'client_index', methods: ['GET'])]
    public function index(
        UserRepository $userRepository,
        PaginatorInterface $paginator,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $perPage = 10,
    ): Response {
        $pagination = $paginator->paginate(
            $userRepository->createClientsQueryBuilder(),
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
        ]);
    }
}
