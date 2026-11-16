<?php

declare(strict_types=1);

namespace App\Seller\Presentation\Http\Controller;

use App\Identity\Domain\Model\Admin;
use App\Seller\Application\Command\ApproveSellerRequest\ApproveSellerRequest;
use App\Seller\Application\Command\ApproveSellerRequest\ApproveSellerRequestHandler;
use App\Seller\Application\Command\RejectSellerRequest\RejectSellerRequest;
use App\Seller\Application\Command\RejectSellerRequest\RejectSellerRequestHandler;
use App\Seller\Domain\Model\SellerRequest;
use App\Seller\Domain\Model\SellerRequestStatus;
use App\Seller\Domain\Repository\SellerRequestRepositoryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Админский список заявок на продажу и решения по ним.
 *
 * CSRF проверяется вручную (isCsrfTokenValid), потому что approve/reject — это не
 * Symfony-формы, а две кнопки в таблице.
 */
#[Route('/admin', 'app_admin_')]
#[IsGranted('ROLE_ADMIN')]
final class AdminSellerRequestController extends AbstractController
{
    public function __construct(
        private readonly SellerRequestRepositoryInterface $requests,
        private readonly ApproveSellerRequestHandler $approveRequest,
        private readonly RejectSellerRequestHandler $rejectRequest,
    ) {
    }

    #[Route('/seller-requests', name: 'seller_request_index', methods: ['GET'])]
    public function index(
        PaginatorInterface $paginator,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $perPage = 15,
        #[MapQueryParameter] ?string $status = null,
    ): Response {
        $statusEnum = null !== $status ? SellerRequestStatus::tryFrom($status) : null;

        $pagination = $paginator->paginate(
            $this->requests->createListQueryBuilder($statusEnum),
            $page,
            $perPage,
        );

        return $this->render('admin/seller_requests/index.html.twig', [
            'admin_menu' => 'seller_requests',
            'pagination' => $pagination,
            'currentStatus' => $status,
        ]);
    }

    #[Route('/seller-requests/{id}/approve', name: 'seller_request_approve', methods: ['POST'])]
    public function approve(SellerRequest $sellerRequest, Request $request): Response
    {
        $admin = $this->requireAdmin($request, $sellerRequest);

        try {
            $seller = ($this->approveRequest)(new ApproveSellerRequest($sellerRequest, $admin));
            $activationUrl = $this->generateUrl('seller_activate', ['token' => $seller->getActivationToken()], 0);
            $this->addFlash('success', 'Заявка одобрена. Ссылка активации: ' . $activationUrl);
        } catch (\RuntimeException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('app_admin_seller_request_index');
    }

    #[Route('/seller-requests/{id}/reject', name: 'seller_request_reject', methods: ['POST'])]
    public function reject(SellerRequest $sellerRequest, Request $request): Response
    {
        $admin = $this->requireAdmin($request, $sellerRequest);

        try {
            ($this->rejectRequest)(new RejectSellerRequest(
                request: $sellerRequest,
                reviewedBy: $admin,
                reason: $request->request->getString('reason') ?: null,
            ));
            $this->addFlash('success', 'Заявка отклонена.');
        } catch (\RuntimeException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('app_admin_seller_request_index');
    }

    private function requireAdmin(Request $request, SellerRequest $sellerRequest): Admin
    {
        if (!$this->isCsrfTokenValid('seller_request_' . $sellerRequest->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $admin = $this->getUser();

        if (!$admin instanceof Admin) {
            throw $this->createAccessDeniedException();
        }

        return $admin;
    }
}
