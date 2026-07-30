<?php

namespace App\Application\PublicProjectionOutbox;

use InvalidArgumentException;

final readonly class PublicProjectionOutboxAttemptCount
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Invalid Public Projection Outbox attempt count.');
        }

        return new self($value);
    }

    public function incremented(): self
    {
        return new self($this->value + 1);
    }
}
