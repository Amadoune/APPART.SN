<?php

namespace App\Application\PublicProjectionOutbox;

use InvalidArgumentException;

final readonly class PublicProjectionOutboxConsumerId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        if (preg_match('/^[a-z][a-z0-9_.-]{2,127}$/', $value) !== 1) {
            throw new InvalidArgumentException('Invalid Public Projection Outbox consumer identity.');
        }

        return new self($value);
    }
}
