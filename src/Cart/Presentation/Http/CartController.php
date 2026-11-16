<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Http;

use App\Cart\Application\Query\CartQueryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart', name: 'cart')]
final class CartController extends AbstractController
{
    public function __construct(
        private readonly CartQueryInterface $cart,
    ) {
    }

    public function __invoke(): Response
    {
        return $this->render('cart/index.html.twig', $this->cart->cartPage());
    }
}
