<?php

namespace App\Application\PublicProjectionDelivery;

use InvalidArgumentException;

final readonly class PublicProjectionDeliveryTraceId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        if ($value === '' || trim($value) !== $value || strlen($value) > 255 || preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
            throw new InvalidArgumentException('Invalid Public Projection Delivery trace identity.');
        }

        return new self($value);
    }
}
