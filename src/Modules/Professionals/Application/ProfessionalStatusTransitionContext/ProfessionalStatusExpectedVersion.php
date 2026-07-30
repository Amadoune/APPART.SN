<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext;

use InvalidArgumentException;

final readonly class ProfessionalStatusExpectedVersion
{
    public function __construct(public int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('The expected professional status version must be positive.');
        }
    }

    public function next(): int
    {
        return $this->value + 1;
    }
}
