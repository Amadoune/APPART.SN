<?php

namespace App\Application\PublicProjectionDelivery;

use InvalidArgumentException;

final readonly class PublicProjectionDeliveryEventIndex
{
    private function __construct(public int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value < 1) {
            throw new InvalidArgumentException('Invalid Public Projection Delivery event index.');
        }

        return new self($value);
    }
}
