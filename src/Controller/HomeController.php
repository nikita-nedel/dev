<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
                'link' => $this->generateUrl('cat', ['slug' => 'odezhda']),
                'buttonText' => 'Смотреть коллекцию'
            ],
            [
                'title' => 'Скидки до 50%',
                'description' => 'Только до конца месяца на зимнюю одежду',
                'image' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80',
                'link' => $this->generateUrl('cat', ['slug' => 'obuv']),
                'buttonText' => 'Смотреть скидки'
            ],
            [
                'title' => 'Бесплатная доставка',
                'description' => 'При заказе от 3000 ₽ по всей России',
                'image' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80',
                'link' => $this->generateUrl('cart'),
                'buttonText' => 'Условия доставки'
            ],
        ];

        $categories = [
            ['id' => 1, 'name' => 'Одежда', 'slug' => 'odezhda', 'icon' => 'bi-handbag', 'count' => 1250],
            ['id' => 2, 'name' => 'Обувь', 'slug' => 'obuv', 'icon' => 'bi-bag', 'count' => 890],
            ['id' => 3, 'name' => 'Аксессуары', 'slug' => 'aksessuary', 'icon' => 'bi-watch', 'count' => 540],
            ['id' => 4, 'name' => 'Электроника', 'slug' => 'elektronika', 'icon' => 'bi-phone', 'count' => 1200],
            ['id' => 5, 'name' => 'Красота', 'slug' => 'krasota', 'icon' => 'bi-droplet', 'count' => 670],
            ['id' => 6, 'name' => 'Дом и сад', 'slug' => 'dom-i-sad', 'icon' => 'bi-house', 'count' => 930],
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

    #[Route('/catalog/{slug}', name: 'cat', defaults: ['slug' => 'odezhda'])]
    public function catalogAction(string $slug): Response
    {
        $categories = $this->getCatalogCategories();
        $category = $categories[$slug] ?? $categories['odezhda'];
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
            'categories' => $categories,
            'brands' => $brands,
            'sizes' => $sizes,
            'colors' => $colors,
            'products' => $products,
        ]);
    }

    #[Route('/cart', name: 'cart')]
    public function cartAction(): Response
    {
        $cartItems = [
            [
                'product' => $this->getProduct(1),
                'size' => '42',
                'color' => 'Белый / красный',
                'quantity' => 1,
            ],
            [
                'product' => $this->getProduct(6),
                'size' => 'One size',
                'color' => 'Чёрный',
                'quantity' => 2,
            ],
            [
                'product' => $this->getProduct(8),
                'size' => 'Набор',
                'color' => 'Rose',
                'quantity' => 1,
            ],
        ];

        $subtotal = array_reduce($cartItems, static function (int $sum, array $item): int {
            return $sum + $item['product']['price'] * $item['quantity'];
        }, 0);
        $discount = 1850;
        $delivery = 0;

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'delivery' => $delivery,
            'total' => $subtotal - $discount + $delivery,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show', requirements: ['id' => '\d+'])]
    public function productAction(int $id): Response
    {
        $product = $this->getProduct($id);
        $relatedProducts = [
            $this->getProduct(2),
            $this->getProduct(4),
            $this->getProduct(6),
            $this->getProduct(7),
        ];

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    #[Route('/orders', name: 'app_orders')]
    #[IsGranted('ROLE_CUSTOMER')]
    public function ordersAction(): Response
    {
        $orders = [
            [
                'number' => 'WB-20481',
                'date' => new \DateTimeImmutable('-1 day'),
                'status' => 'В пути',
                'statusType' => 'shipping',
                'total' => 12980,
                'address' => 'ПВЗ WB Style, Москва, ул. Лесная, 7',
                'products' => [
                    $this->getProduct(1),
                    $this->getProduct(6),
                ],
                'steps' => ['Оформлен', 'Оплачен', 'Собран', 'В пути'],
                'activeStep' => 3,
            ],
            [
                'number' => 'WB-20396',
                'date' => new \DateTimeImmutable('-8 days'),
                'status' => 'Получен',
                'statusType' => 'done',
                'total' => 7980,
                'address' => 'Курьером до двери',
                'products' => [
                    $this->getProduct(4),
                    $this->getProduct(8),
                ],
                'steps' => ['Оформлен', 'Оплачен', 'Доставлен', 'Получен'],
                'activeStep' => 4,
            ],
            [
                'number' => 'WB-20142',
                'date' => new \DateTimeImmutable('-21 days'),
                'status' => 'Получен',
                'statusType' => 'done',
                'total' => 29990,
                'address' => 'ПВЗ WB Style, Санкт-Петербург, Невский пр., 45',
                'products' => [
                    $this->getProduct(5),
                ],
                'steps' => ['Оформлен', 'Оплачен', 'Доставлен', 'Получен'],
                'activeStep' => 4,
            ],
        ];

        return $this->render('orders/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_CUSTOMER')]
    public function profileAction(): Response
    {
        $profileStats = [
            ['label' => 'Заказов', 'value' => 18, 'icon' => 'bi-bag-check'],
            ['label' => 'В избранном', 'value' => 34, 'icon' => 'bi-heart'],
            ['label' => 'Бонусов', 'value' => '4 820', 'icon' => 'bi-stars'],
            ['label' => 'Отзывы', 'value' => 12, 'icon' => 'bi-chat-heart'],
        ];

        $quickActions = [
            ['title' => 'Мои заказы', 'description' => 'Отследить доставку и повторить покупку', 'icon' => 'bi-box-seam', 'url' => $this->generateUrl('app_orders')],
            ['title' => 'Адреса доставки', 'description' => 'Дом, офис и пункты выдачи', 'icon' => 'bi-geo-alt', 'url' => $this->generateUrl('app_profile')],
            ['title' => 'Платежи', 'description' => 'Карты, бонусы и сертификаты', 'icon' => 'bi-credit-card', 'url' => $this->generateUrl('cart')],
            ['title' => 'Уведомления', 'description' => 'Скидки, статусы заказов и рекомендации', 'icon' => 'bi-bell', 'url' => $this->generateUrl('app_profile')],
        ];

        $recentOrders = [
            [
                'number' => 'WB-20481',
                'title' => 'Nike Air Max 270, рюкзак Nike',
                'status' => 'В пути',
                'date' => '25 июня',
                'total' => 12980,
                'progress' => 70,
            ],
            [
                'number' => 'WB-20396',
                'title' => 'Платье вечернее, косметический набор',
                'status' => 'Получен',
                'date' => '18 июня',
                'total' => 7980,
                'progress' => 100,
            ],
            [
                'number' => 'WB-20142',
                'title' => 'Sony WH-1000XM5',
                'status' => 'Получен',
                'date' => '4 июня',
                'total' => 29990,
                'progress' => 100,
            ],
        ];

        $activity = [
            ['text' => 'Добавлены в избранное часы Casio G-Shock', 'time' => 'Сегодня, 14:20', 'icon' => 'bi-heart-fill'],
            ['text' => 'Оставлен отзыв на кроссовки Nike Air Max', 'time' => 'Вчера, 19:05', 'icon' => 'bi-star-fill'],
            ['text' => 'Начислены бонусы за заказ WB-20396', 'time' => '18 июня', 'icon' => 'bi-gift-fill'],
        ];

        return $this->render('profile/index.html.twig', [
            'profileStats' => $profileStats,
            'quickActions' => $quickActions,
            'recentOrders' => $recentOrders,
            'activity' => $activity,
        ]);
    }

    private function getCatalogCategories(): array
    {
        return [
            'odezhda' => [
                'id' => 1,
                'name' => 'Одежда',
                'slug' => 'odezhda',
                'description' => 'Мужская и женская одежда: куртки, платья, футболки, джинсы. Новые коллекции и скидки до 50%.',
                'productCount' => 1250,
                'avgRating' => 4.7,
                'maxDiscount' => 50,
            ],
            'obuv' => [
                'id' => 2,
                'name' => 'Обувь',
                'slug' => 'obuv',
                'description' => 'Кроссовки, ботинки, туфли и сезонная обувь от популярных брендов с быстрой доставкой.',
                'productCount' => 890,
                'avgRating' => 4.8,
                'maxDiscount' => 35,
            ],
            'aksessuary' => [
                'id' => 3,
                'name' => 'Аксессуары',
                'slug' => 'aksessuary',
                'description' => 'Сумки, часы, украшения и практичные детали, которые собирают образ до конца.',
                'productCount' => 540,
                'avgRating' => 4.6,
                'maxDiscount' => 40,
            ],
            'elektronika' => [
                'id' => 4,
                'name' => 'Электроника',
                'slug' => 'elektronika',
                'description' => 'Смартфоны, наушники, гаджеты для дома и работы с официальной гарантией.',
                'productCount' => 1200,
                'avgRating' => 4.9,
                'maxDiscount' => 25,
            ],
            'krasota' => [
                'id' => 5,
                'name' => 'Красота',
                'slug' => 'krasota',
                'description' => 'Уходовая косметика, макияж, парфюм и наборы для ежедневных ритуалов.',
                'productCount' => 670,
                'avgRating' => 4.5,
                'maxDiscount' => 45,
            ],
            'dom-i-sad' => [
                'id' => 6,
                'name' => 'Дом и сад',
                'slug' => 'dom-i-sad',
                'description' => 'Текстиль, декор, хранение и товары для уютного дома и дачи.',
                'productCount' => 930,
                'avgRating' => 4.7,
                'maxDiscount' => 30,
            ],
        ];
    }

    private function getProduct(int $id): array
    {
        $products = [
            1 => [
                'id' => 1,
                'name' => 'Стильные кроссовки Nike Air Max',
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1608231387042-66d1773070a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                ],
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
                'category' => 'Обувь',
                'description' => 'Лёгкие кроссовки для города и тренировок с мягкой амортизацией, цепкой подошвой и узнаваемым силуэтом Air Max.',
                'colors' => ['Белый / красный', 'Чёрный', 'Серый'],
                'sizes' => ['39', '40', '41', '42', '43', '44'],
                'features' => ['Дышащий верх из текстиля', 'Амортизация Air Max', 'Резиновая подошва', 'Гарантия 30 дней'],
            ],
            2 => [
                'id' => 2,
                'name' => 'Кожаная куртка мужская',
                'image' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1551028719-00167b16eac5?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1520975954732-35dd22299614?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                ],
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
                'category' => 'Одежда',
                'description' => 'Куртка прямого кроя из мягкой экокожи с плотной подкладкой и металлической фурнитурой.',
                'colors' => ['Чёрный', 'Коричневый'],
                'sizes' => ['S', 'M', 'L', 'XL'],
                'features' => ['Ветрозащитный материал', 'Два внутренних кармана', 'Плотная молния', 'Подкладка из вискозы'],
            ],
            3 => [
                'id' => 3,
                'name' => 'Смартфон Samsung Galaxy S23',
                'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1598327105666-5b89351aff97?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                ],
                'price' => 79990,
                'oldPrice' => 89990,
                'discount' => 11,
                'rating' => 4.9,
                'reviews' => 234,
                'sellerName' => 'Samsung Official',
                'sellerRating' => 4.9,
                'isNew' => true,
                'isHit' => false,
                'isFavorite' => false,
                'freeShipping' => true,
                'category' => 'Электроника',
                'description' => 'Флагманский смартфон с ярким AMOLED-дисплеем, мощной камерой и высокой производительностью для работы и развлечений.',
                'colors' => ['Чёрный', 'Лавандовый', 'Зелёный'],
                'sizes' => ['128 ГБ', '256 ГБ'],
                'features' => ['AMOLED 120 Гц', 'Тройная камера', 'Быстрая зарядка', 'Защита IP68'],
            ],
            4 => [
                'id' => 4,
                'name' => 'Женское платье вечернее',
                'image' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1595777457583-95e059d581b8?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1568252542512-9fe8fe9c87bb?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                ],
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
                'category' => 'Одежда',
                'description' => 'Элегантное платье миди с мягким силуэтом, акцентом на талии и тканью с лёгким сиянием.',
                'colors' => ['Чёрный', 'Изумрудный', 'Пудровый'],
                'sizes' => ['XS', 'S', 'M', 'L'],
                'features' => ['Подходит для вечера', 'Не мнётся в дороге', 'Пояс в комплекте', 'Деликатная стирка'],
            ],
            5 => [
                'id' => 5,
                'name' => 'Наушники Sony WH-1000XM5',
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1546435770-a3e426bf472b?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                ],
                'price' => 29990,
                'oldPrice' => 34990,
                'discount' => 14,
                'rating' => 4.7,
                'reviews' => 167,
                'sellerName' => 'Sony Center',
                'sellerRating' => 4.8,
                'isNew' => false,
                'isHit' => true,
                'isFavorite' => true,
                'freeShipping' => true,
                'category' => 'Электроника',
                'description' => 'Беспроводные наушники с активным шумоподавлением, чистым звуком и автономностью до 30 часов.',
                'colors' => ['Чёрный', 'Серебристый'],
                'sizes' => ['One size'],
                'features' => ['Шумоподавление', 'До 30 часов работы', 'Быстрая зарядка', 'Bluetooth 5.2'],
            ],
            6 => [
                'id' => 6,
                'name' => 'Рюкзак спортивный Nike',
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                ],
                'price' => 3990,
                'oldPrice' => null,
                'discount' => 0,
                'rating' => 4.4,
                'reviews' => 42,
                'sellerName' => 'Nike Store',
                'sellerRating' => 4.9,
                'isNew' => true,
                'isHit' => false,
                'isFavorite' => false,
                'freeShipping' => true,
                'category' => 'Аксессуары',
                'description' => 'Вместительный рюкзак для спорта и города с отделением для ноутбука и водоотталкивающей пропиткой.',
                'colors' => ['Чёрный', 'Синий'],
                'sizes' => ['One size'],
                'features' => ['Объём 24 л', 'Отделение для ноутбука', 'Мягкие лямки', 'Карман для бутылки'],
            ],
            7 => [
                'id' => 7,
                'name' => 'Часы мужские Casio G-Shock',
                'image' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1434056886845-dac89ffe9b56?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                ],
                'price' => 15990,
                'oldPrice' => 19990,
                'discount' => 20,
                'rating' => 4.6,
                'reviews' => 78,
                'sellerName' => 'Watch Shop',
                'sellerRating' => 4.7,
                'isNew' => false,
                'isHit' => true,
                'isFavorite' => false,
                'freeShipping' => true,
                'category' => 'Аксессуары',
                'description' => 'Ударопрочные часы с водозащитой, подсветкой и спортивным дизайном для ежедневного использования.',
                'colors' => ['Чёрный'],
                'sizes' => ['One size'],
                'features' => ['Водозащита 200 м', 'Подсветка', 'Будильник', 'Ударопрочный корпус'],
            ],
            8 => [
                'id' => 8,
                'name' => 'Косметический набор L\'Oréal',
                'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1596462502278-27bfdc403348?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                ],
                'price' => 2490,
                'oldPrice' => 3290,
                'discount' => 24,
                'rating' => 4.2,
                'reviews' => 34,
                'sellerName' => 'Beauty Shop',
                'sellerRating' => 4.4,
                'isNew' => true,
                'isHit' => false,
                'isFavorite' => false,
                'freeShipping' => false,
                'category' => 'Красота',
                'description' => 'Подарочный набор для ухода и макияжа: базовые средства в стильной упаковке.',
                'colors' => ['Rose', 'Nude'],
                'sizes' => ['Набор'],
                'features' => ['Подарочная упаковка', 'Для ежедневного ухода', 'Подходит для поездок', 'Срок годности 24 месяца'],
            ],
            9 => [
                'id' => 9,
                'name' => 'Хлопковая футболка oversize',
                'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1503341504253-dff4815485f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
                ],
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
                'category' => 'Одежда',
                'description' => 'Свободная базовая футболка из плотного хлопка, которая держит форму и легко сочетается с джинсами, шортами и жакетами.',
                'colors' => ['Белый', 'Чёрный', 'Графит'],
                'sizes' => ['S', 'M', 'L', 'XL'],
                'features' => ['100% хлопок', 'Плотность 220 г/м²', 'Свободная посадка', 'Не просвечивает'],
            ],
        ];

        return $products[$id] ?? $products[1];
    }
}
