<?php

namespace App\Application\RuntimeHealth;

final readonly class RuntimeHealthRegistration
{
    /** @param list<RuntimeHealthComponent> $missingDependencies */
    public function __construct(
        public RuntimeHealthComponent $component,
        public bool $registered,
        public ?object $implementation,
        public bool $configured = true,
        public array $missingDependencies = [],
    ) {}
}
