<?php

namespace App\Application\PublicProjectionDelivery;

use InvalidArgumentException;

final readonly class PublicProjectionDeliveryAggregateType
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        if (preg_match('/^[A-Z][A-Za-z0-9]{2,63}$/', $value) !== 1) {
            throw new InvalidArgumentException('Invalid Public Projection Delivery aggregate type.');
        }

        return new self($value);
    }
}
