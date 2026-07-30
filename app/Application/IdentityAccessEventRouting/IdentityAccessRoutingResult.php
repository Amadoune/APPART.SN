<?php

namespace App\Application\IdentityAccessEventRouting;

use App\Application\IdentityAccessEventTransport\IdentityAccessDeliveryMessageV1;

final readonly class IdentityAccessRoutingResult
{
    /** @param list<IdentityAccessRoutingDestination> $destinations */
    private function __construct(
        public bool $routed,
        public array $destinations,
        public ?string $diagnostic,
        public IdentityAccessDeliveryMessageV1 $message,
    ) {}

    /** @param list<IdentityAccessRoutingDestination> $destinations */
    public static function routed(IdentityAccessDeliveryMessageV1 $message, array $destinations): self
    {
        return new self(true, $destinations, null, $message);
    }

    public static function rejected(IdentityAccessDeliveryMessageV1 $message, string $diagnostic): self
    {
        return new self(false, [], $diagnostic, $message);
    }
}
