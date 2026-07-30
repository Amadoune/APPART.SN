<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract;

use InvalidArgumentException;

final readonly class LeadLifecycleContextChecksum
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{64}$/', $value) !== 1) {
            throw new InvalidArgumentException('The lead lifecycle context checksum must be SHA-256.');
        }

        return new self($value);
    }
}
