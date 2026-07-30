<?php

namespace Appart\Modules\Professionals\Domain\ValueObject;

use Appart\Modules\Professionals\Domain\Exception\InvalidProfessionalValue;

final readonly class RepresentativeId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = trim($value);
        if (preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]{2,127}$/', $value) !== 1) {
            throw InvalidProfessionalValue::field('representative_id');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
