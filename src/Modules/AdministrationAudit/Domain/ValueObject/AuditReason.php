<?php

namespace Appart\Modules\AdministrationAudit\Domain\ValueObject;

use Appart\Modules\AdministrationAudit\Domain\Exception\InvalidAuditValue;

final readonly class AuditReason
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = preg_replace('/\s+/u', ' ', trim($value));
        if (! is_string($value) || mb_strlen($value) < 10 || mb_strlen($value) > 1000) {
            throw InvalidAuditValue::field('audit_reason');
        }

        return new self($value);
    }
}
