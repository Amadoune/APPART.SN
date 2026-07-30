<?php

namespace Appart\Modules\Professionals\Domain\ValueObject;

use InvalidArgumentException;

final readonly class ProfessionalStatusActorId
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException('The professional status actor identifier must be a UUID.');
        }

        return new self($value);
    }
}
