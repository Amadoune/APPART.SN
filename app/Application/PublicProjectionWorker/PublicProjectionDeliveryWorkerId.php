<?php

namespace App\Application\PublicProjectionWorker;

use InvalidArgumentException;

final readonly class PublicProjectionDeliveryWorkerId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        if ($value === '' || trim($value) !== $value) {
            throw new InvalidArgumentException('Worker id must be non-empty and already normalized.');
        }

        return new self($value);
    }
}
