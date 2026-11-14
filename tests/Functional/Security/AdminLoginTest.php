<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Tests\Functional\FunctionalTestCase;

final class AdminLoginTest extends FunctionalTestCase
{
    public function testLoginPageRenders(): void
    {
        $this->client->request('GET', '/admin/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name="_username"]');
    }

    public function testAdminCanLogin(): void
    {
        $this->createAdmin('admin@example.com', 'password123');

        $this->client->request('POST', '/admin/login', [
            '_username' => 'admin@example.com',
            '_password' => 'password123',
        ]);

        $this->assertRedirectedTo('/admin/dashboard');
    }

    public function testInactiveAdminIsRejectedByUserChecker(): void
    {
        $this->createAdmin('admin@example.com', 'password123', active: false);

        $this->client->request('POST', '/admin/login', [
            '_username' => 'admin@example.com',
            '_password' => 'password123',
        ]);

        $this->assertRedirectedTo('/admin/login');
    }
}
