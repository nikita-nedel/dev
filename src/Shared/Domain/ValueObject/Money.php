<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Exception\CurrencyMismatchException;

final readonly class Money
{
    private const int SCALE = 2;

    private function __construct(
        private int $amount,
        private string $currency,
    ) {
        if (1 !== preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new \InvalidArgumentException(sprintf('Currency must be a 3-letter ISO code, "%s" given.', $currency));
        }
    }

    public static function of(int $amount, string $currency): self
    {
        return new self($amount, $currency);
    }

    public static function zero(string $currency): self
    {
        return new self(0, $currency);
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        $this->guardSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->guardSameCurrency($other);

        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(int $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    public function isZero(): bool
    {
        return 0 === $this->amount;
    }

    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    public function isGreaterThan(self $other): bool
    {
        $this->guardSameCurrency($other);

        return $this->amount > $other->amount;
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency && $this->amount === $other->amount;
    }

    /**
     * "1299 BYN" -> "12.99".
     */
    public function toDecimalString(): string
    {
        $absolute = abs($this->amount);
        $sign = $this->amount < 0 ? '-' : '';

        return sprintf('%s%d.%0' . self::SCALE . 'd', $sign, intdiv($absolute, 10 ** self::SCALE), $absolute % 10 ** self::SCALE);
    }

    public function format(): string
    {
        return $this->toDecimalString() . ' ' . $this->currency;
    }

    public function __toString(): string
    {
        return $this->format();
    }

    private function guardSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw CurrencyMismatchException::between($this->currency, $other->currency);
        }
    }
}
