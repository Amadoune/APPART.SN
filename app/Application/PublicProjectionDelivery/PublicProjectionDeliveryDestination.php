<?php

namespace App\Application\PublicProjectionDelivery;

use InvalidArgumentException;

final readonly class PublicProjectionDeliveryDestination
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        if (preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/D', $value) !== 1) {
            throw new InvalidArgumentException('Public Projection Delivery destination must be non-empty and canonical.');
        }

        return new self($value);
    }
}
