<?php

namespace App\Application\RuntimeHealth;

final readonly class RuntimeHealthResult
{
    /** @param list<RuntimeHealthDiagnostic> $diagnostics */
    public function __construct(public RuntimeHealthStatus $status, public array $diagnostics) {}
}
