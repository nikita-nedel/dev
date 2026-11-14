<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\InvalidUuidException;
use App\Shared\Domain\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

final class UuidTest extends TestCase
{
    public function testGeneratesVersion7Uuid(): void
    {
        $uuid = Uuid::generate();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $uuid->value(),
        );
    }

    public function testGeneratedUuidCarriesCurrentTime(): void
    {
        $before = new \DateTimeImmutable('-1 minute');

        $uuid = Uuid::generate();

        $after = new \DateTimeImmutable('+1 minute');

        // UuidV7::getDateTime() читает те самые 48 бит метки времени из значения.
        $embedded = SymfonyUuid::fromString($uuid->value())->getDateTime();

        self::assertGreaterThanOrEqual($before, $embedded);
        self::assertLessThanOrEqual($after, $embedded);
    }

    public function testGeneratedValuesAreUnique(): void
    {
        self::assertNotSame(Uuid::generate()->value(), Uuid::generate()->value());
    }

    public function testVersion7SortsByCreationTime(): void
    {
        $earlier = Uuid::fromString('0192f1a8-0000-7000-8000-000000000000');
        $later = Uuid::fromString('0192f1a8-0001-7fff-8fff-ffffffffffff');

        self::assertLessThan(0, strcmp($earlier->value(), $later->value()));
    }

    public function testFromStringNormalizesCase(): void
    {
        $uuid = Uuid::fromString('0192F1A8-0000-7000-8000-000000000000');

        self::assertSame('0192f1a8-0000-7000-8000-000000000000', $uuid->value());
    }

    public function testRoundTripThroughString(): void
    {
        $uuid = Uuid::generate();

        self::assertTrue($uuid->equals(Uuid::fromString((string) $uuid)));
    }

    public function testDifferentUuidsAreNotEqual(): void
    {
        self::assertFalse(Uuid::generate()->equals(Uuid::generate()));
    }

    public function testRejectsInvalidValue(): void
    {
        $this->expectException(InvalidUuidException::class);

        Uuid::fromString('not-a-uuid');
    }

    public function testInvalidValueKeepsOriginalException(): void
    {
        try {
            Uuid::fromString('not-a-uuid');
            self::fail('Ожидалось InvalidUuidException.');
        } catch (InvalidUuidException $exception) {
            self::assertInstanceOf(\InvalidArgumentException::class, $exception->getPrevious());
        }
    }
}
