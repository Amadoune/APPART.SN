<?php

namespace App\Application\RuntimeHealth;

use InvalidArgumentException;

final readonly class RuntimeHealthRequirement
{
    /** @param class-string $contract */
    public function __construct(
        public RuntimeHealthComponent $component,
        public string $contract,
        public bool $required = true,
    ) {
        if (trim($contract) === '') {
            throw new InvalidArgumentException('A Runtime health contract is required.');
        }
    }
}
