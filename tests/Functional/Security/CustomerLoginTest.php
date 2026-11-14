<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Tests\Functional\FunctionalTestCase;

final class CustomerLoginTest extends FunctionalTestCase
{
    public function testLoginPageRenders(): void
    {
        $this->client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name="_username"]');
        self::assertSelectorExists('input[name="_password"]');
    }

    public function testCustomerCanLogin(): void
    {
        $this->createCustomer('login@example.com', 'password123');

        $this->client->request('POST', '/login', [
            '_username' => 'login@example.com',
            '_password' => 'password123',
        ]);

        $this->assertRedirectedTo('/');

        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
    }

    public function testLoginWithWrongPasswordIsRejected(): void
    {
        $this->createCustomer('login@example.com', 'password123');

        $this->client->request('POST', '/login', [
            '_username' => 'login@example.com',
            '_password' => 'wrong-password',
        ]);

        $this->assertRedirectedTo('/login');
    }

    public function testLoginWithUnknownEmailIsRejected(): void
    {
        $this->client->request('POST', '/login', [
            '_username' => 'nobody@example.com',
            '_password' => 'password123',
        ]);

        $this->assertRedirectedTo('/login');
    }
}
