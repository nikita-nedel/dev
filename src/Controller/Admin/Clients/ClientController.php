<?php

declare(strict_types=1);

namespace App\Controller\Admin\Clients;

use App\Controller\Admin\DashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', 'app_admin_')]
class ClientController extends DashboardController
{
    #[Route('/client', name: 'client_index', methods: ['GET'])]
    public function index(): Response
    {
        $clients = [
            [
                'id' => 1,
                'name' => 'Иван Петров',
                'email' => 'ivan.petrov@mail.ru',
                'phone' => '+7 (916) 123-45-67',
                'registeredAt' => new \DateTimeImmutable('-2 years'),
                'ordersCount' => 24,
                'totalSpent' => 187_450,
                'status' => 'active',
                'statusLabel' => 'Активен',
            ],
            [
                'id' => 2,
                'name' => 'Мария Сидорова',
                'email' => 'maria.sidorova@gmail.com',
                'phone' => '+7 (903) 987-65-43',
                'registeredAt' => new \DateTimeImmutable('-8 months'),
                'ordersCount' => 12,
                'totalSpent' => 54_890,
                'status' => 'active',
                'statusLabel' => 'Активен',
            ],
            [
                'id' => 3,
                'name' => 'Алексей Козлов',
                'email' => 'a.kozlov@yandex.ru',
                'phone' => '+7 (925) 555-12-34',
                'registeredAt' => new \DateTimeImmutable('-3 days'),
                'ordersCount' => 1,
                'totalSpent' => 8_990,
                'status' => 'new',
                'statusLabel' => 'Новый',
            ],
            [
                'id' => 4,
                'name' => 'Елена Новикова',
                'email' => 'elena.novikova@mail.ru',
                'phone' => '+7 (977) 234-56-78',
                'registeredAt' => new \DateTimeImmutable('-1 year'),
                'ordersCount' => 0,
                'totalSpent' => 0,
                'status' => 'inactive',
                'statusLabel' => 'Неактивен',
            ],
            [
                'id' => 5,
                'name' => 'Дмитрий Волков',
                'email' => 'd.volkov@inbox.ru',
                'phone' => '+7 (915) 876-54-32',
                'registeredAt' => new \DateTimeImmutable('-6 months'),
                'ordersCount' => 7,
                'totalSpent' => 32_100,
                'status' => 'active',
                'statusLabel' => 'Активен',
            ],
            [
                'id' => 6,
                'name' => 'Анна Морозова',
                'email' => 'anna.morozova@gmail.com',
                'phone' => '+7 (926) 111-22-33',
                'registeredAt' => new \DateTimeImmutable('-4 months'),
                'ordersCount' => 18,
                'totalSpent' => 124_600,
                'status' => 'active',
                'statusLabel' => 'Активен',
            ],
            [
                'id' => 7,
                'name' => 'Сергей Белов',
                'email' => 's.belov@mail.ru',
                'phone' => '+7 (903) 444-55-66',
                'registeredAt' => new \DateTimeImmutable('-2 weeks'),
                'ordersCount' => 3,
                'totalSpent' => 15_200,
                'status' => 'new',
                'statusLabel' => 'Новый',
            ],
            [
                'id' => 8,
                'name' => 'Ольга Кузнецова',
                'email' => 'olga.kuznetsova@yandex.ru',
                'phone' => '+7 (916) 777-88-99',
                'registeredAt' => new \DateTimeImmutable('-3 years'),
                'ordersCount' => 41,
                'totalSpent' => 312_800,
                'status' => 'active',
                'statusLabel' => 'Активен',
            ],
            [
                'id' => 9,
                'name' => 'Павел Орлов',
                'email' => 'p.orlov@gmail.com',
                'phone' => '+7 (925) 333-44-55',
                'registeredAt' => new \DateTimeImmutable('-5 months'),
                'ordersCount' => 2,
                'totalSpent' => 4_590,
                'status' => 'blocked',
                'statusLabel' => 'Заблокирован',
            ],
            [
                'id' => 10,
                'name' => 'Татьяна Лебедева',
                'email' => 't.lebedeva@mail.ru',
                'phone' => '+7 (977) 666-77-88',
                'registeredAt' => new \DateTimeImmutable('-1 month'),
                'ordersCount' => 5,
                'totalSpent' => 28_900,
                'status' => 'active',
                'statusLabel' => 'Активен',
            ],
        ];

        $stats = [
            'total' => count($clients),
            'active' => count(array_filter($clients, fn (array $c) => $c['status'] === 'active')),
            'new' => count(array_filter($clients, fn (array $c) => $c['status'] === 'new')),
            'blocked' => count(array_filter($clients, fn (array $c) => $c['status'] === 'blocked')),
        ];

        return $this->render('admin/clients/index.html.twig', [
            'admin_menu' => 'clients',
            'clients' => $clients,
            'stats' => $stats,
        ]);
    }
}
