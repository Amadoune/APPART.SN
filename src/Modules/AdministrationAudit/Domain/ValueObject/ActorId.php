<?php

namespace Appart\Modules\AdministrationAudit\Domain\ValueObject;

use Appart\Modules\AdministrationAudit\Domain\Exception\InvalidAuditValue;

final readonly class ActorId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);
        if (preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]{2,127}$/', $value) !== 1) {
            throw InvalidAuditValue::field('actor_id');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
