<?php

declare(strict_types=1);

namespace App\Controller\Seller\Products;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/seller', 'app_seller_')]
class ProductIndexController extends AbstractController
{
    #[Route('/products', 'products')]
    public function index(): Response
    {
        $categories = [
            ['id' => 1, 'name' => 'Электроника'],
            ['id' => 2, 'name' => 'Одежда'],
            ['id' => 3, 'name' => 'Дом и сад'],
        ];

        $products = [
            [
                'id' => 1,
                'name' => 'Смартфон X',
                'sku' => 'PH-001',
                'category' => $categories[0],
                'price' => 29990,
                'stock' => 45,
                'active' => true,
                'image' => null,
            ],
            [
                'id' => 2,
                'name' => 'Беспроводные наушники',
                'sku' => 'AU-202',
                'category' => $categories[0],
                'price' => 4990,
                'stock' => 120,
                'active' => true,
                'image' => null,
            ],
            [
                'id' => 3,
                'name' => 'Футболка хлопок',
                'sku' => 'CL-012',
                'category' => $categories[1],
                'price' => 1290,
                'stock' => 0,
                'active' => false,
                'image' => null,
            ],
            [
                'id' => 4,
                'name' => 'Набор садовых инструментов',
                'sku' => 'GR-007',
                'category' => $categories[2],
                'price' => 3490,
                'stock' => 8,
                'active' => true,
                'image' => null,
            ],
        ];

        return $this->render('seller/products/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    #[Route('/products/new', name: 'products_new')]
    public function create(): Response
    {
        $categories = [
            ['id' => 1, 'name' => 'Электроника'],
            ['id' => 2, 'name' => 'Одежда'],
            ['id' => 3, 'name' => 'Дом и сад'],
        ];

        return $this->render('seller/products/form.html.twig', [
            'product' => null, // новый товар
            'categories' => $categories,
        ]);
    }

    #[Route('/products/{id}/edit', name: 'products_edit')]
    public function update(int $id): Response
    {
        $categories = [
            ['id' => 1, 'name' => 'Электроника'],
            ['id' => 2, 'name' => 'Одежда'],
            ['id' => 3, 'name' => 'Дом и сад'],
        ];

        $product = [
            'id' => $id,
            'name' => 'Смартфон X',
            'sku' => 'PH-001',
            'description' => 'Отличный смартфон',
            'category' => $categories[0],
            'price' => 29990,
            'stock' => 45,
            'active' => true,
            'image' => null,
        ];

        return $this->render('seller/products/form.html.twig', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }
}
