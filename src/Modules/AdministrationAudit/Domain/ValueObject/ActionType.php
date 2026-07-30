<?php

namespace Appart\Modules\AdministrationAudit\Domain\ValueObject;

use Appart\Modules\AdministrationAudit\Domain\Exception\InvalidAuditValue;

final readonly class ActionType
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[a-z][a-z0-9_.-]{2,63}$/', $value) !== 1) {
            throw InvalidAuditValue::field('action_type');
        }

        return new self($value);
    }
}
