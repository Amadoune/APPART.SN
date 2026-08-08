<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead;

use InvalidArgumentException;

final readonly class ReservationAvailabilityIntentId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        return new self(self::uuid($value, 'availability intent'));
    }

    private static function uuid(string $value, string $label): string
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException("The reservation {$label} id must be a UUID.");
        }

        return $value;
    }
}
