<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

use Appart\Modules\ContactsLeads\Domain\Exception\InvalidLeadValue;

final readonly class VisitorIdentityId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw InvalidLeadValue::field('visitor_identity_id');
        }

        return new self($value);
    }
}
