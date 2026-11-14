<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\InvalidUuidException;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

final readonly class Uuid
{
    private function __construct(private SymfonyUuid $value)
    {
    }

    public static function generate(): self
    {
        return new self(SymfonyUuid::v7());
    }

    public static function fromString(string $value): self
    {
        try {
            return new self(SymfonyUuid::fromString(trim($value)));
        } catch (\InvalidArgumentException $exception) {
            throw InvalidUuidException::forValue($value, $exception);
        }
    }

    public function value(): string
    {
        return $this->value->toRfc4122();
    }

    public function equals(self $other): bool
    {
        return $this->value->equals($other->value);
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
