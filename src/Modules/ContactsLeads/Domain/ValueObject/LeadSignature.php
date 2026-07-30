<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

final readonly class LeadSignature
{
    private function __construct(public string $value) {}

    public static function fromCanonicalInput(string $input): self
    {
        return new self(hash('sha256', $input));
    }
}
