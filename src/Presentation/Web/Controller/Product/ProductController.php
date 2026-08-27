<?php

declare(strict_types=1);

namespace App\Presentation\Web\Controller\Product;

use App\Domain\Product\Repository\ProductRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products', name: 'product_')]
class ProductController extends AbstractController
{
    #[Route(path: '', name: 'create', methods: [Request::METHOD_POST, Request::METHOD_GET])]
    public function createProductAction(Request $request, ProductRepositoryInterface $repository)
    {
        dd($request->getRequestUri(), $repository);
    }
}
