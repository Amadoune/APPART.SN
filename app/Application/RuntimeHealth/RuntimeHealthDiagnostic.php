<?php

namespace App\Application\RuntimeHealth;

final readonly class RuntimeHealthDiagnostic
{
    public function __construct(
        public RuntimeHealthComponent $component,
        public RuntimeHealthDiagnosticCode $code,
        public bool $required,
        public ?RuntimeHealthComponent $dependency = null,
    ) {}
}
