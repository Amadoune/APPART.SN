<?php

namespace Appart\Modules\AdministrationAudit\Domain\ValueObject;

use Appart\Modules\AdministrationAudit\Domain\Exception\InvalidAuditValue;

final readonly class TargetResourceId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);
        if (preg_match('/^[a-z][a-z0-9_-]{1,31}:[A-Za-z0-9][A-Za-z0-9._:-]{1,127}$/', $value) !== 1) {
            throw InvalidAuditValue::field('target_resource_id');
        }

        return new self($value);
    }
}
