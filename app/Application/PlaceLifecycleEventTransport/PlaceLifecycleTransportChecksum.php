<?php

namespace App\Application\PlaceLifecycleEventTransport;

final readonly class PlaceLifecycleTransportChecksum
{
    private function __construct(public string $value) {}

    public static function fromCanonicalPayload(string $canonicalPayload): self
    {
        return new self(hash('sha256', $canonicalPayload));
    }
}
