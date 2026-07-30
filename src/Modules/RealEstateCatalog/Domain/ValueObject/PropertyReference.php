<?php

namespace Appart\Modules\RealEstateCatalog\Domain\ValueObject;

use Appart\Modules\RealEstateCatalog\Domain\Exception\InvalidPropertyValue;

final readonly class PropertyReference
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtoupper(trim($value));
        if (preg_match('/^[A-Z0-9][A-Z0-9._\/-]{3,63}$/', $value) !== 1) {
            throw InvalidPropertyValue::field('property_reference');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
