<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

final class CurrencyMismatchException extends DomainException
{
    public static function between(string $left, string $right): self
    {
        return new self(sprintf('Cannot operate on different currencies: %s and %s.', $left, $right));
    }
}
