<?php

namespace App\Application\PublicProjectionOutbox;

use InvalidArgumentException;

final readonly class PublicProjectionOutboxClaimOwnerId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        if ($value === '' || trim($value) !== $value || strlen($value) > 255) {
            throw new InvalidArgumentException('Invalid Public Projection Outbox claim owner.');
        }

        return new self($value);
    }
}
