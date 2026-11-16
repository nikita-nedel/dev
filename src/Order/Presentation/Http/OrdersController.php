<?php

declare(strict_types=1);

namespace App\Order\Presentation\Http;

use App\Order\Application\Query\OrdersQueryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/orders', name: 'app_orders')]
#[IsGranted('ROLE_CUSTOMER')]
final class OrdersController extends AbstractController
{
    public function __construct(
        private readonly OrdersQueryInterface $orders,
    ) {
    }

    public function __invoke(): Response
    {
        return $this->render('orders/index.html.twig', $this->orders->ordersPage());
    }
}
