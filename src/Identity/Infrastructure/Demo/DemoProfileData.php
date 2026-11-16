<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Demo;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DemoProfileData
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
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

        return [
            'profileStats' => $profileStats,
            'quickActions' => $quickActions,
            'recentOrders' => $recentOrders,
            'activity' => $activity,
        ];
    }
}
