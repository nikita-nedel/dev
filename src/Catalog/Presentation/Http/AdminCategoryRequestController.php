<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http;

use App\Catalog\Application\Command\ApproveCategoryRequest\ApproveCategoryRequest;
use App\Catalog\Application\Command\ApproveCategoryRequest\ApproveCategoryRequestHandler;
use App\Catalog\Application\Command\RejectCategoryRequest\RejectCategoryRequest;
use App\Catalog\Application\Command\RejectCategoryRequest\RejectCategoryRequestHandler;
use App\Catalog\Domain\Model\CategoryRequest;
use App\Catalog\Domain\Model\CategoryRequestStatus;
use App\Catalog\Domain\Repository\CategoryRequestRepositoryInterface;
use App\Identity\Domain\Model\Admin;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/category-requests', name: 'app_admin_category_request_')]
#[IsGranted('ROLE_ADMIN')]
final class AdminCategoryRequestController extends AbstractController
{
    public function __construct(
        private readonly CategoryRequestRepositoryInterface $requests,
        private readonly ApproveCategoryRequestHandler $approveRequest,
        private readonly RejectCategoryRequestHandler $rejectRequest,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        PaginatorInterface $paginator,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] ?string $status = null,
    ): Response {
        $statusEnum = null !== $status ? CategoryRequestStatus::tryFrom($status) : null;

        $pagination = $paginator->paginate(
            $this->requests->createListQueryBuilder($statusEnum),
            $page,
            15,
        );

        return $this->render('admin/category_requests/index.html.twig', [
            'admin_menu' => 'category_requests',
            'pagination' => $pagination,
            'currentStatus' => $status,
        ]);
    }

    #[Route('/{id}/approve', name: 'approve', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function approve(CategoryRequest $categoryRequest, Request $request): Response
    {
        $admin = $this->requireAdmin($request, $categoryRequest);

        try {
            ($this->approveRequest)(new ApproveCategoryRequest($categoryRequest, $admin));
            $this->addFlash('success', 'Заявка одобрена: категория создана.');
        } catch (\RuntimeException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('app_admin_category_request_index');
    }

    #[Route('/{id}/reject', name: 'reject', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function reject(CategoryRequest $categoryRequest, Request $request): Response
    {
        $admin = $this->requireAdmin($request, $categoryRequest);

        try {
            ($this->rejectRequest)(new RejectCategoryRequest(
                request: $categoryRequest,
                reviewedBy: $admin,
                reason: $request->request->getString('reason'),
            ));
            $this->addFlash('success', 'Заявка отклонена.');
        } catch (\RuntimeException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('app_admin_category_request_index');
    }

    private function requireAdmin(Request $request, CategoryRequest $categoryRequest): Admin
    {
        $tokenId = 'category_request_' . $categoryRequest->getId();

        if (!$this->isCsrfTokenValid($tokenId, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $admin = $this->getUser();

        if (!$admin instanceof Admin) {
            throw $this->createAccessDeniedException();
        }

        return $admin;
    }
}
