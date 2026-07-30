<?php

namespace Appart\Modules\RealEstateCatalog\Domain\ValueObject;

use Appart\Modules\RealEstateCatalog\Domain\Exception\InvalidPropertyValue;

final readonly class AddressLine
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = preg_replace('/\s+/u', ' ', trim($value));
        if (! is_string($value) || mb_strlen($value) < 3 || mb_strlen($value) > 255) {
            throw InvalidPropertyValue::field('address_line');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
