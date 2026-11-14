<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

final class InvalidUuidException extends DomainException
{
    public static function forValue(string $value, ?\Throwable $previous = null): self
    {
        return new self(sprintf('"%s" is not a valid UUID.', $value), 0, $previous);
    }
}
