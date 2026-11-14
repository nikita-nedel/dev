<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Tests\Functional\FunctionalTestCase;

final class SellerLoginTest extends FunctionalTestCase
{
    public function testActivatedSellerCanLogin(): void
    {
        $customer = $this->createCustomer('seller@example.com', 'password123');
        $this->createSeller($customer);

        $this->client->request('POST', '/seller/login', [
            '_username' => 'seller@example.com',
            '_password' => 'password123',
        ]);

        $this->assertRedirectedTo('/seller/dashboard');
    }

    public function testNotActivatedSellerIsRejectedByUserChecker(): void
    {
        $customer = $this->createCustomer('seller@example.com', 'password123');
        $this->createSeller($customer, active: false);

        $this->client->request('POST', '/seller/login', [
            '_username' => 'seller@example.com',
            '_password' => 'password123',
        ]);

        $this->assertRedirectedTo('/seller/login');
    }
}
