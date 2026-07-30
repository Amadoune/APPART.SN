<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

use Appart\Modules\ContactsLeads\Domain\Exception\InvalidLeadValue;

final readonly class ContactMessage
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));
        if (mb_strlen($value) < 10 || mb_strlen($value) > 2000) {
            throw InvalidLeadValue::field('contact_message');
        }

        return new self($value);
    }
}
