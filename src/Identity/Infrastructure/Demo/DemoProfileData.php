<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Demo;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class DemoProfileData
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function profilePage(): array
    {
        $profileStats = [
            ['label' => 'Заказов', 'value' => 18, 'icon' => 'bi-bag-check'],
            ['label' => 'В избранном', 'value' => 34, 'icon' => 'bi-heart'],
            ['label' => 'Бонусов', 'value' => '4 820', 'icon' => 'bi-stars'],
            ['label' => 'Отзывы', 'value' => 12, 'icon' => 'bi-chat-heart'],
        ];

        $quickActions = [
            ['title' => 'Мои заказы', 'description' => 'Отследить доставку и повторить покупку', 'icon' => 'bi-box-seam', 'url' => $this->urlGenerator->generate('app_orders')],
            ['title' => 'Адреса доставки', 'description' => 'Дом, офис и пункты выдачи', 'icon' => 'bi-geo-alt', 'url' => $this->urlGenerator->generate('app_profile')],
            ['title' => 'Платежи', 'description' => 'Карты, бонусы и сертификаты', 'icon' => 'bi-credit-card', 'url' => $this->urlGenerator->generate('cart')],
            ['title' => 'Уведомления', 'description' => 'Скидки, статусы заказов и рекомендации', 'icon' => 'bi-bell', 'url' => $this->urlGenerator->generate('app_profile')],
        ];

        $recentOrders = [
            [
                'number' => 'WB-20481',
                'title' => 'Кроссовки Nike Air Max · Рюкзак Nike',
                'status' => 'В пути',
                'date' => '25 июня',
                'total' => 12980,
                'itemCount' => 2,
                'meta' => 'Ожидается 19 ноября · ПВЗ на Лесной, 7',
                'images' => [
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=160&q=80',
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?ixlib=rb-4.0.3&auto=format&fit=crop&w=160&q=80',
                ],
                'progress' => 70,
            ],
            [
                'number' => 'WB-20396',
                'title' => 'Платье вечернее · Косметический набор L’Oréal',
                'status' => 'Получен',
                'date' => '18 июня',
                'total' => 7980,
                'itemCount' => 2,
                'meta' => 'Получен 20 июня · ПВЗ на Лесной, 7',
                'images' => [
                    'https://images.unsplash.com/photo-1595777457583-95e059d581b8?ixlib=rb-4.0.3&auto=format&fit=crop&w=160&q=80',
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=160&q=80',
                ],
                'progress' => 100,
            ],
            [
                'number' => 'WB-20142',
                'title' => 'Наушники Sony WH-1000XM5',
                'status' => 'Получен',
                'date' => '4 июня',
                'total' => 29990,
                'itemCount' => 1,
                'meta' => 'Получен 6 июня · Курьером',
                'images' => [
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=160&q=80',
                ],
                'progress' => 100,
            ],
        ];

        $activity = [
            ['text' => 'Добавлены в избранное часы Studio', 'time' => 'Сегодня, 14:20', 'icon' => 'bi-heart-fill'],
            ['text' => 'Оставлен отзыв на лоферы Soft', 'time' => 'Вчера, 19:05', 'icon' => 'bi-star-fill'],
            ['text' => 'Начислены бонусы за заказ WB-20396', 'time' => '18 июня', 'icon' => 'bi-gift-fill'],
        ];

        return [
            'profileStats' => $profileStats,
            'quickActions' => $quickActions,
            'recentOrders' => $recentOrders,
            'activity' => $activity,
        ];
    }
}
