<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Catalog\Domain\Model\Category;
use App\Identity\Domain\Model\Admin;
use App\Identity\Domain\Model\AdminProfile;
use App\Identity\Domain\Model\Customer;
use App\Identity\Domain\Model\Seller;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class FunctionalTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->truncateDatabase();
    }

    protected function em(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function passwordHasher(): UserPasswordHasherInterface
    {
        return static::getContainer()->get('security.user_password_hasher');
    }

    protected function createCustomer(string $email = 'customer@example.com', string $password = 'password123'): Customer
    {
        $customer = new Customer()
            ->setEmail($email)
            ->setRoles(['ROLE_CUSTOMER']);

        $customer->setPassword($this->passwordHasher()->hashPassword($customer, $password));

        $em = $this->em();
        $em->persist($customer);
        $em->flush();

        return $customer;
    }

    protected function createAdmin(
        string $email = 'admin@example.com',
        string $password = 'password123',
        bool $active = true,
    ): Admin {
        $admin = new Admin()
            ->setEmail($email)
            ->setRoles(['ROLE_ADMIN'])
            ->setIsActive($active);

        $admin->setPassword($this->passwordHasher()->hashPassword($admin, $password));
        $admin->setProfile(
            new AdminProfile()
                ->setFirstName('Админ')
                ->setLastName('Тестовый'),
        );

        $em = $this->em();
        $em->persist($admin);
        $em->flush();

        return $admin;
    }

    protected function createSeller(
        Customer $customer,
        bool $active = true,
        string $password = 'password123',
    ): Seller {
        $seller = new Seller()
            ->setEmail((string) $customer->getEmail())
            ->setCustomer($customer)
            ->setRoles(['ROLE_SELLER'])
            ->setIsActive($active);

        if ($active) {
            $seller->setPassword($this->passwordHasher()->hashPassword($seller, $password));
        }

        $em = $this->em();
        $em->persist($seller);
        $em->flush();

        return $seller;
    }

    protected function createCategory(string $name = 'Одежда', ?Category $parent = null): Category
    {
        $category = Category::create(
            parent: $parent,
            name: $name,
            slug: 'test-' . bin2hex(random_bytes(4)),
        );

        $em = $this->em();
        $em->persist($category);
        $em->flush();

        return $category;
    }

    protected function assertRedirectedTo(string $path): void
    {
        self::assertResponseStatusCodeSame(302);

        $location = (string) $this->client->getResponse()->headers->get('Location');
        $actualPath = (string) parse_url($location, PHP_URL_PATH);

        self::assertSame(
            $path,
            $actualPath,
            sprintf('Ожидался redirect на "%s", получен "%s".', $path, $location),
        );
    }

    private function truncateDatabase(): void
    {
        $connection = $this->em()->getConnection();

        $tables = $connection->fetchFirstColumn(
            "SELECT tablename FROM pg_tables WHERE schemaname = 'public' AND tablename <> 'doctrine_migration_versions'",
        );

        if ([] === $tables) {
            return;
        }

        $quoted = array_map(static fn (string $table): string => '"' . $table . '"', $tables);

        $connection->executeStatement(
            'TRUNCATE TABLE ' . implode(', ', $quoted) . ' RESTART IDENTITY CASCADE',
        );
    }
}
