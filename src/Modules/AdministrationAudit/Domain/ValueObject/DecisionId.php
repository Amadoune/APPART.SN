<?php

namespace Appart\Modules\AdministrationAudit\Domain\ValueObject;

use Appart\Modules\AdministrationAudit\Domain\Exception\InvalidAuditValue;

final readonly class DecisionId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw InvalidAuditValue::field('decision_id');
        }

        return new self($value);
    }
}
