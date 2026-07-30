<?php

namespace Appart\Modules\Professionals\Domain\ValueObject;

use Appart\Modules\Professionals\Domain\Exception\InvalidProfessionalValue;

final readonly class EstablishmentId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw InvalidProfessionalValue::field('establishment_id');
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
