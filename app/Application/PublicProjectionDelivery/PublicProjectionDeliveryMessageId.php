<?php

namespace App\Application\PublicProjectionDelivery;

use InvalidArgumentException;

final readonly class PublicProjectionDeliveryMessageId
{
    private function __construct(public string $value) {}

    public static function fromIdempotencyKey(PublicProjectionDeliveryIdempotencyKey $key): self
    {
        return new self('ppd-message:'.$key->value);
    }

    public static function fromString(string $value): self
    {
        if ($value === '' || trim($value) !== $value || ! str_starts_with($value, 'ppd-message:')) {
            throw new InvalidArgumentException('Invalid Public Projection Delivery message identity.');
        }

        return new self($value);
    }
}
