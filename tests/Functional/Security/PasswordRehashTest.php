<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Identity\Domain\Model\Customer;
use App\Tests\Functional\FunctionalTestCase;

final class PasswordRehashTest extends FunctionalTestCase
{
    public function testOutdatedPasswordHashIsUpgradedOnLogin(): void
    {
        $legacyHash = password_hash('password123', PASSWORD_BCRYPT, ['cost' => 4]);

        $customer = new Customer()
            ->setEmail('legacy@example.com')
            ->setRoles(['ROLE_CUSTOMER']);
        $customer->setPassword($legacyHash);

        $em = $this->em();
        $em->persist($customer);
        $em->flush();

        $this->client->request('POST', '/login', [
            '_username' => 'legacy@example.com',
            '_password' => 'password123',
        ]);

        $this->assertRedirectedTo('/');

        $this->em()->clear();
        $stored = $this->em()->getRepository(Customer::class)->findOneBy(['email' => 'legacy@example.com']);
        self::assertInstanceOf(Customer::class, $stored);

        self::assertNotSame($legacyHash, $stored->getPassword(), 'Хэш должен быть перезаписан');
        self::assertStringStartsWith('$2y$05$', (string) $stored->getPassword(), 'Новый хэш — bcrypt cost 5');
        self::assertTrue(password_verify('password123', (string) $stored->getPassword()), 'Пароль остаётся тем же');
    }

    public function testLoginStillWorksWithFreshHash(): void
    {
        $this->createCustomer('fresh@example.com', 'password123');

        $this->client->request('POST', '/login', [
            '_username' => 'fresh@example.com',
            '_password' => 'password123',
        ]);

        $this->assertRedirectedTo('/');
    }
}
