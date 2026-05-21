<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function testAction(): Response
    {
        $slides = [
            [
                'title' => 'Осенняя коллекция',
                'description' => 'Новые поступления модной одежды и обуви',
                'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80',
                'link' => '#',
                'buttonText' => 'Смотреть коллекцию'
            ],
            [
                'title' => 'Скидки до 50%',
                'description' => 'Только до конца месяца на зимнюю одежду',
                'image' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80',
                'link' => '#',
                'buttonText' => 'Смотреть скидки'
            ],
            [
                'title' => 'Бесплатная доставка',
                'description' => 'При заказе от 3000 ₽ по всей России',
                'image' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80',
                'link' => '#',
                'buttonText' => 'Условия доставки'
            ],
        ];

        $categories = [
            ['id' => 1, 'name' => 'Одежда', 'icon' => 'bi-tshirt', 'count' => 1250],
            ['id' => 2, 'name' => 'Обувь', 'icon' => 'bi-bag', 'count' => 890],
            ['id' => 3, 'name' => 'Аксессуары', 'icon' => 'bi-watch', 'count' => 540],
            ['id' => 4, 'name' => 'Электроника', 'icon' => 'bi-phone', 'count' => 1200],
            ['id' => 5, 'name' => 'Красота', 'icon' => 'bi-droplet', 'count' => 670],
            ['id' => 6, 'name' => 'Дом и сад', 'icon' => 'bi-house', 'count' => 930],
        ];

        $products = [
            [
                'id' => 1,
                'name' => 'Стильные кроссовки Nike Air Max',
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'price' => 8990,
                'oldPrice' => 11990,
                'discount' => 25,
                'rating' => 4.5,
                'reviews' => 128,
                'sellerName' => 'Nike Store',
                'isNew' => true,
                'isHit' => true,
                'isFavorite' => false
            ],
            [
                'id' => 2,
                'name' => 'Кожаная куртка мужская',
                'image' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'price' => 12990,
                'oldPrice' => null,
                'discount' => 0,
                'rating' => 4.8,
                'reviews' => 56,
                'sellerName' => 'Leather Shop',
                'isNew' => false,
                'isHit' => true,
                'isFavorite' => true
            ],
            [
                'id' => 3,
                'name' => 'Смартфон Samsung Galaxy S23',
                'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'price' => 79990,
                'oldPrice' => 89990,
                'discount' => 11,
                'rating' => 4.9,
                'reviews' => 234,
                'sellerName' => 'Samsung Official',
                'isNew' => true,
                'isHit' => false,
                'isFavorite' => false
            ],
            [
                'id' => 4,
                'name' => 'Женское платье вечернее',
                'image' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'price' => 5490,
                'oldPrice' => 7990,
                'discount' => 31,
                'rating' => 4.3,
                'reviews' => 89,
                'sellerName' => 'Fashion Store',
                'isNew' => true,
                'isHit' => false,
                'isFavorite' => false
            ],
            [
                'id' => 5,
                'name' => 'Наушники Sony WH-1000XM5',
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'price' => 29990,
                'oldPrice' => 34990,
                'discount' => 14,
                'rating' => 4.7,
                'reviews' => 167,
                'sellerName' => 'Sony Center',
                'isNew' => false,
                'isHit' => true,
                'isFavorite' => true
            ],
            [
                'id' => 6,
                'name' => 'Рюкзак спортивный Nike',
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'price' => 3990,
                'oldPrice' => null,
                'discount' => 0,
                'rating' => 4.4,
                'reviews' => 42,
                'sellerName' => 'Nike Store',
                'isNew' => true,
                'isHit' => false,
                'isFavorite' => false
            ],
            [
                'id' => 7,
                'name' => 'Часы мужские Casio G-Shock',
                'image' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'price' => 15990,
                'oldPrice' => 19990,
                'discount' => 20,
                'rating' => 4.6,
                'reviews' => 78,
                'sellerName' => 'Watch Shop',
                'isNew' => false,
                'isHit' => true,
                'isFavorite' => false
            ],
            [
                'id' => 8,
                'name' => 'Косметический набор L\'Oréal',
                'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'price' => 2490,
                'oldPrice' => 3290,
                'discount' => 24,
                'rating' => 4.2,
                'reviews' => 34,
                'sellerName' => 'Beauty Shop',
                'isNew' => true,
                'isHit' => false,
                'isFavorite' => false
            ],
        ];

        return $this->render('home/index.html.twig', [
            'slides' => $slides,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    #[Route('/catalog', name: 'cat')]
    public function catalogAction(): Response
    {
        $category = [
            'id' => 1,
            'name' => 'Одежда',
            'slug' => 'odezhda',
            'description' => 'Мужская и женская одежда: куртки, платья, футболки, джинсы. Новые коллекции и скидки до 50%.',
            'productCount' => 1250,
            'avgRating' => 4.7,
            'maxDiscount' => 50,
        ];
        $brands = [
            ['id' => 1, 'name' => 'Nike', 'count' => 124],
            ['id' => 2, 'name' => 'Adidas', 'count' => 98],
            ['id' => 3, 'name' => 'Zara', 'count' => 156],
            ['id' => 4, 'name' => 'H&M', 'count' => 87],
            ['id' => 5, 'name' => 'Uniqlo', 'count' => 64],
        ];
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        $colors = [
            ['name' => 'Чёрный', 'code' => '#000000'],
            ['name' => 'Белый', 'code' => '#ffffff'],
            ['name' => 'Серый', 'code' => '#6b7280'],
            ['name' => 'Синий', 'code' => '#3b82f6'],
            ['name' => 'Красный', 'code' => '#ef4444'],
            ['name' => 'Бежевый', 'code' => '#d4b896'],
        ];
        $products = [
            [
                'id' => 1,
                'name' => 'Стильные кроссовки Nike Air Max',
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'price' => 8990,
                'oldPrice' => 11990,
                'discount' => 25,
                'rating' => 4.5,
                'reviews' => 128,
                'sellerName' => 'Nike Store',
                'sellerRating' => 4.9,
                'isNew' => true,
                'isHit' => true,
                'isFavorite' => false,
                'freeShipping' => true,
            ],
            [
                'id' => 2,
                'name' => 'Кожаная куртка мужская',
                'image' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'price' => 12990,
                'oldPrice' => null,
                'discount' => 0,
                'rating' => 4.8,
                'reviews' => 56,
                'sellerName' => 'Leather Shop',
                'sellerRating' => 4.6,
                'isNew' => false,
                'isHit' => true,
                'isFavorite' => true,
                'freeShipping' => false,
            ],
            [
                'id' => 4,
                'name' => 'Женское платье вечернее',
                'image' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'price' => 5490,
                'oldPrice' => 7990,
                'discount' => 31,
                'rating' => 4.3,
                'reviews' => 89,
                'sellerName' => 'Fashion Store',
                'sellerRating' => 4.5,
                'isNew' => true,
                'isHit' => false,
                'isFavorite' => false,
                'freeShipping' => true,
            ],
            [
                'id' => 9,
                'name' => 'Хлопковая футболка oversize',
                'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80',
                'price' => 1990,
                'oldPrice' => 2990,
                'discount' => 33,
                'rating' => 4.4,
                'reviews' => 212,
                'sellerName' => 'Basic Wear',
                'sellerRating' => 4.7,
                'isNew' => false,
                'isHit' => true,
                'isFavorite' => false,
                'freeShipping' => true,
            ],
        ];
        return $this->render('catalog/index.html.twig', [
            'category' => $category,
            'brands' => $brands,
            'sizes' => $sizes,
            'colors' => $colors,
            'products' => $products,
        ]);    }

    #[Route('/cart', name: 'cart')]
    public function cartAction()
    {

    }
}
