<?php

namespace Appart\Modules\RealEstateCatalog\Domain\ValueObject;

use Appart\Modules\RealEstateCatalog\Domain\Exception\InvalidPropertyValue;

final readonly class PropertyId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        return new self(self::uuid($value, 'property_id'));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private static function uuid(string $value, string $field): string
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw InvalidPropertyValue::field($field);
        }

        return $value;
    }
}
