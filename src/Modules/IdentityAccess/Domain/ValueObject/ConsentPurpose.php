<?php

namespace Appart\Modules\IdentityAccess\Domain\ValueObject;

use Appart\Modules\IdentityAccess\Domain\Exception\InvalidIdentityValue;

final readonly class ConsentPurpose
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[a-z][a-z0-9_.-]{2,63}$/', $value) !== 1) {
            throw InvalidIdentityValue::forField('consent_purpose');
        }

        return new self($value);
    }
}
