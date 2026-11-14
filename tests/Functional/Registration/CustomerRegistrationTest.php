<?php

declare(strict_types=1);

namespace App\Tests\Functional\Registration;

use App\Entity\Customer;
use App\Tests\Functional\FunctionalTestCase;

final class CustomerRegistrationTest extends FunctionalTestCase
{
    public function testRegistrationPageRenders(): void
    {
        $this->client->request('GET', '/register');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name="customer_registration_form"]');
    }

    public function testCustomerIsRegisteredWithProfile(): void
    {
        $this->client->request('POST', '/register', [
            'customer_registration_form' => [
                'firstName' => 'Иван',
                'lastName' => 'Петров',
                'phone' => '+375291234567',
                'email' => 'new@example.com',
                'plainPassword' => 'password123',
            ],
        ]);

        self::assertResponseStatusCodeSame(302);

        $customer = $this->em()->getRepository(Customer::class)->findOneBy(['email' => 'new@example.com']);
        self::assertInstanceOf(Customer::class, $customer);
        self::assertNotNull($customer->getPassword());
        self::assertSame('Петров', $customer->getProfile()?->getLastName());
        self::assertSame('+375291234567', $customer->getPhone());
    }

    public function testDuplicateEmailIsNotRegistered(): void
    {
        $this->createCustomer('taken@example.com');

        $this->client->request('POST', '/register', [
            'customer_registration_form' => [
                'firstName' => 'Иван',
                'lastName' => 'Петров',
                'phone' => '+375299999999',
                'email' => 'taken@example.com',
                'plainPassword' => 'password123',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertCount(1, $this->em()->getRepository(Customer::class)->findAll());
    }

    public function testShortPasswordIsNotAccepted(): void
    {
        $this->client->request('POST', '/register', [
            'customer_registration_form' => [
                'firstName' => 'Иван',
                'lastName' => 'Петров',
                'phone' => '+375291234567',
                'email' => 'new@example.com',
                'plainPassword' => 'short',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertCount(0, $this->em()->getRepository(Customer::class)->findAll());
    }
}
