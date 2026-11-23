<?php

declare(strict_types=1);

namespace App\Seller\Presentation\Http\Controller;

use App\Identity\Domain\Model\Seller;
use App\Seller\Application\Service\SellerOverviewProvider;
use App\Seller\Domain\Model\Shop;
use App\Seller\Domain\Repository\SellerRequestRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SELLER')]
final class SellerDashboardController extends AbstractController
{
    public function __construct(
        private readonly SellerRequestRepositoryInterface $requests,
        private readonly SellerOverviewProvider $overview,
    ) {
    }

    #[Route('/seller/dashboard', name: 'app_seller_dashboard', methods: ['GET'])]
    public function index(): Response
    {
        $seller = $this->getUser();

        if (!$seller instanceof Seller) {
            throw $this->createAccessDeniedException();
        }

        $overview = $this->overview->forSeller($seller);
        $shops = array_map(static fn (array $row): Shop => $row['shop'], $overview['shops']);

        return $this->render('seller/dashboard.html.twig', [
            'seller' => $seller,
            'sellerRequest' => $this->requests->findBySeller($seller),
            'shops' => $shops,
            'overview' => $overview,
        ]);
    }
}
