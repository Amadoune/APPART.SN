<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext;

use InvalidArgumentException;

final readonly class AdministrativeActionExpectedVersion
{
    public function __construct(public int $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('The expected lifecycle version cannot be negative.');
        }
    }

    public function next(): int
    {
        return $this->value + 1;
    }
}
