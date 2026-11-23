<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject;

use App\Shared\Domain\Exception\DomainException;

final readonly class Sku
{
    public const int MIN_LENGTH = 3;
    public const int MAX_LENGTH = 64;

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $value = strtoupper(trim($value));

        if (mb_strlen($value) < self::MIN_LENGTH || mb_strlen($value) > self::MAX_LENGTH) {
            $message = sprintf(
                'Артикул должен содержать от %d до %d символов.',
                self::MIN_LENGTH,
                self::MAX_LENGTH,
            );

            throw new DomainException($message);
        }

        if (1 !== preg_match('/^[A-Z0-9][A-Z0-9._-]*$/', $value)) {
            throw new DomainException('Артикул может содержать только латинские буквы, цифры, точку, дефис и подчёркивание.');
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
