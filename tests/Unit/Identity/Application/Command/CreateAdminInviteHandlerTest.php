<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Application\Command;

use App\Identity\Application\Command\CreateAdminInvite\CreateAdminInvite;
use App\Identity\Application\Command\CreateAdminInvite\CreateAdminInviteHandler;
use App\Identity\Domain\Model\AdminInvite;
use App\Identity\Domain\Repository\AdminInviteRepositoryInterface;
use App\Tests\Support\FixedClock;
use App\Tests\Support\PassthroughTransactionManager;
use PHPUnit\Framework\TestCase;

final class CreateAdminInviteHandlerTest extends TestCase
{
    public function testInviteExpiresInTwoDaysFromNow(): void
    {
        $clock = new FixedClock(new \DateTimeImmutable('2026-01-01 12:00:00'));
        $invites = new InMemoryAdminInviteRepository();

        $handler = new CreateAdminInviteHandler($invites, $clock, new PassthroughTransactionManager());

        $invite = $handler(new CreateAdminInvite(email: 'new-admin@example.com'));

        self::assertSame('new-admin@example.com', $invite->getEmail());
        self::assertSame('2026-01-03 12:00:00', $invite->getExpiresAt()->format('Y-m-d H:i:s'));
        self::assertCount(1, $invites->all);
        self::assertSame(64, \strlen($invite->getToken()), 'Токен — 32 случайных байта в hex');
    }

    public function testInviteRespectsCustomTtl(): void
    {
        $clock = new FixedClock(new \DateTimeImmutable('2026-01-01 12:00:00'));

        $handler = new CreateAdminInviteHandler(
            new InMemoryAdminInviteRepository(),
            $clock,
            new PassthroughTransactionManager(),
        );

        $invite = $handler(new CreateAdminInvite(email: 'a@example.com', ttlDays: 7));

        self::assertSame('2026-01-08 12:00:00', $invite->getExpiresAt()->format('Y-m-d H:i:s'));
    }
}

final class InMemoryAdminInviteRepository implements AdminInviteRepositoryInterface
{
    /**
     * @var list<AdminInvite>
     */
    public array $all = [];

    public function add(AdminInvite $invite): void
    {
        $this->all[] = $invite;
    }

    public function findValidByToken(string $token): ?AdminInvite
    {
        foreach ($this->all as $invite) {
            if ($invite->getToken() === $token) {
                return $invite;
            }
        }

        return null;
    }
}
