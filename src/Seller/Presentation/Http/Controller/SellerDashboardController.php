<?php

declare(strict_types=1);

namespace App\Seller\Presentation\Http\Controller;

use App\Identity\Domain\Model\Seller;
use App\Seller\Domain\Repository\SellerRequestRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Кабинет продавца: данные продавца и статус его заявки.
 */
#[IsGranted('ROLE_SELLER')]
final class SellerDashboardController extends AbstractController
{
    public function __construct(
        private readonly SellerRequestRepositoryInterface $requests,
    ) {
    }

    #[Route('/seller/dashboard', name: 'app_seller_dashboard', methods: ['GET'])]
    public function index(): Response
    {
        $seller = $this->getUser();

        if (!$seller instanceof Seller) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('seller/dashboard.html.twig', [
            'seller' => $seller,
            'sellerRequest' => $this->requests->findBySeller($seller),
        ]);
    }
}
