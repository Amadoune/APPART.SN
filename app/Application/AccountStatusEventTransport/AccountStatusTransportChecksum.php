<?php

namespace App\Application\AccountStatusEventTransport;

final readonly class AccountStatusTransportChecksum
{
    private function __construct(public string $value) {}

    public static function fromCanonicalPayload(string $canonicalPayload): self
    {
        return new self(hash('sha256', $canonicalPayload));
    }
}
