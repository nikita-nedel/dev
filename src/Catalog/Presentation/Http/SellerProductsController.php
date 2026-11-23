<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http;

use App\Catalog\Application\Query\SellerCatalogQueryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/seller', 'app_seller_')]
final class SellerProductsController extends AbstractController
{
    public function __construct(
        private readonly SellerCatalogQueryInterface $catalog,
    ) {
    }

    #[Route('/products', 'products')]
    public function index(): Response
    {
        return $this->render('seller/products/index.html.twig', [
            'products' => $this->catalog->products(),
            'categories' => $this->catalog->categories(),
        ]);
    }

    #[Route('/products/new', name: 'products_new')]
    public function create(): Response
    {
        return $this->render('seller/products/form.html.twig', [
            'product' => null,
            'categories' => $this->catalog->categories(),
        ]);
    }

    #[Route('/products/{id}/edit', name: 'products_edit')]
    public function update(int $id): Response
    {
        $product = array_find(
            $this->catalog->products(),
            static fn (array $candidate): bool => $id === $candidate['id'],
        );

        if (null === $product) {
            throw $this->createNotFoundException('Товар не найден.');
        }

        return $this->render('seller/products/form.html.twig', [
            'product' => $product,
            'categories' => $this->catalog->categories(),
        ]);
    }
}
