<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext;

use InvalidArgumentException;

final readonly class ProfessionalStatusContextChecksum
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{64}$/', $value) !== 1) {
            throw new InvalidArgumentException('The professional status context checksum must be SHA-256.');
        }

        return new self($value);
    }
}
