<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

abstract class AbstractDomainEvent implements DomainEventInterface
{
    private \DateTimeImmutable $occurredOn;

    public function __construct(?\DateTimeImmutable $occurredOn = null)
    {
        $this->occurredOn = $occurredOn ?? new \DateTimeImmutable();
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function eventName(): string
    {
        return new \ReflectionClass($this)->getShortName();
    }
}
