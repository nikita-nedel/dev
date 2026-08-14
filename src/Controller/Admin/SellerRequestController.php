<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\SellerRequest;
use App\Enum\SellerRequestStatus;
use App\Repository\SellerRequestRepository;
use App\Service\SellerRequestService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', 'app_admin_')]
#[IsGranted('ROLE_ADMIN')]
class SellerRequestController extends AbstractController
{
    public function __construct(
        private readonly SellerRequestService $sellerRequestService,
        private readonly SellerRequestRepository $sellerRequestRepository,
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
            $this->sellerRequestRepository->createListQueryBuilder($statusEnum),
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
        if (!$this->isCsrfTokenValid('seller_request_' . $sellerRequest->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $admin = $this->getUser();
        if (!$admin instanceof Admin) {
            throw $this->createAccessDeniedException();
        }

        try {
            $seller = $this->sellerRequestService->approve($sellerRequest, $admin);
            $activationUrl = $this->generateUrl(
                'seller_activate',
                ['token' => $seller->getActivationToken()],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );

            $this->addFlash(
                'success',
                sprintf(
                    'Заявка одобрена. Ссылка для установки пароля продавца: <a href="%1$s" target="_blank" rel="noopener">%1$s</a>',
                    htmlspecialchars($activationUrl, ENT_QUOTES),
                ),
            );
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_seller_request_index');
    }

    #[Route('/seller-requests/{id}/reject', name: 'seller_request_reject', methods: ['POST'])]
    public function reject(SellerRequest $sellerRequest, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('seller_request_' . $sellerRequest->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $admin = $this->getUser();
        if (!$admin instanceof Admin) {
            throw $this->createAccessDeniedException();
        }

        try {
            $this->sellerRequestService->reject(
                $sellerRequest,
                $admin,
                $request->request->getString('reason') ?: null,
            );
            $this->addFlash('success', 'Заявка отклонена.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_seller_request_index');
    }
}
