<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Entity\Admin;
use App\Entity\AdminInvite;
use App\Tests\Functional\FunctionalTestCase;

final class AdminInviteTest extends FunctionalTestCase
{
    public function testAdminCanCreateInvite(): void
    {
        $admin = $this->createAdmin();
        $this->client->loginUser($admin, 'admin_context');

        $this->client->request('POST', '/admin/admins/invite', [
            'admin_invite_create_form' => ['email' => 'invitee@example.com'],
        ]);

        self::assertResponseIsSuccessful();

        $invite = $this->em()->getRepository(AdminInvite::class)->findOneBy(['email' => 'invitee@example.com']);
        self::assertInstanceOf(AdminInvite::class, $invite);
        self::assertNull($invite->getUsedAt());
        self::assertTrue($invite->isValid());
    }

    public function testAdminCanRegisterFromInvite(): void
    {
        $admin = $this->createAdmin();
        $this->client->loginUser($admin, 'admin_context');

        $this->client->request('POST', '/admin/admins/invite', [
            'admin_invite_create_form' => ['email' => 'invitee@example.com'],
        ]);

        $invite = $this->em()->getRepository(AdminInvite::class)->findOneBy(['email' => 'invitee@example.com']);
        self::assertInstanceOf(AdminInvite::class, $invite);

        $this->client->request('GET', '/admin/invite/' . $invite->getToken());
        self::assertResponseIsSuccessful();

        $this->client->request('POST', '/admin/invite/' . $invite->getToken(), [
            'admin_invite_registration_form' => [
                'firstName' => 'Иван',
                'lastName' => 'Петров',
                'plainPassword' => ['first' => 'password123', 'second' => 'password123'],
            ],
        ]);

        self::assertResponseStatusCodeSame(302);

        $this->em()->clear();
        $createdAdmin = $this->em()->getRepository(Admin::class)->findOneBy(['email' => 'invitee@example.com']);
        self::assertInstanceOf(Admin::class, $createdAdmin);
        self::assertSame('Петров', $createdAdmin->getProfile()?->getLastName());

        $usedInvite = $this->em()->getRepository(AdminInvite::class)->find($invite->getId());
        self::assertNotNull($usedInvite?->getUsedAt());
    }

    public function testUnknownInviteTokenIsRejected(): void
    {
        $this->client->request('GET', '/admin/invite/unknown-token');

        $this->assertRedirectedTo('/admin/login');
    }
}
