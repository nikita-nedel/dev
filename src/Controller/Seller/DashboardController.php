<?php

declare(strict_types=1);

namespace App\Controller\Seller;

use App\Entity\Seller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/seller')]
#[IsGranted('ROLE_SELLER')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_seller_dashboard', methods: ['GET'])]
    public function index(): Response
    {
        $seller = $this->getUser();
        if (!$seller instanceof Seller) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('seller/dashboard.html.twig', [
            'seller' => $seller,
            'sellerRequest' => $seller->getRequest(),
        ]);
    }
}
