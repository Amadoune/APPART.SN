<?php

namespace App\Application\RuntimeHealth;

use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;

final readonly class DeterministicRuntimeHealthInspector implements RuntimeHealthInspector
{
    /**
     * @param  list<RuntimeHealthRequirement>  $requirements
     * @param  list<RuntimeHealthRegistration>  $registrations
     */
    public function __construct(private array $requirements, private array $registrations) {}

    public function inspect(): RuntimeHealthResult
    {
        $registered = [];
        foreach ($this->registrations as $registration) {
            $registered[$registration->component->value] = $registration;
        }
        $diagnostics = [];
        foreach ($this->requirements as $requirement) {
            $registration = $registered[$requirement->component->value] ?? null;
            if ($registration === null) {
                $diagnostics[] = new RuntimeHealthDiagnostic($requirement->component, RuntimeHealthDiagnosticCode::ComponentAbsent, $requirement->required);

                continue;
            }
            if (! $registration->registered || $registration->implementation === null) {
                $diagnostics[] = new RuntimeHealthDiagnostic($requirement->component, RuntimeHealthDiagnosticCode::ImplementationNotRegistered, $requirement->required);
            } elseif (! $registration->configured || (! interface_exists($requirement->contract) && ! class_exists($requirement->contract))) {
                $diagnostics[] = new RuntimeHealthDiagnostic($requirement->component, RuntimeHealthDiagnosticCode::InvalidConfiguration, $requirement->required);
            } elseif (! is_a($registration->implementation, $requirement->contract)) {
                $diagnostics[] = new RuntimeHealthDiagnostic($requirement->component, RuntimeHealthDiagnosticCode::IncompatibleContract, $requirement->required);
            }
            foreach ($registration->missingDependencies as $dependency) {
                $diagnostics[] = new RuntimeHealthDiagnostic($requirement->component, RuntimeHealthDiagnosticCode::DependencyMissing, $requirement->required, $dependency);
            }
        }
        usort($diagnostics, static fn (RuntimeHealthDiagnostic $left, RuntimeHealthDiagnostic $right): int => [
            $left->component->value,
            $left->code->value,
            $left->dependency === null ? '' : $left->dependency->value,
        ] <=> [
            $right->component->value,
            $right->code->value,
            $right->dependency === null ? '' : $right->dependency->value,
        ]);
        $status = RuntimeHealthStatus::Healthy;
        foreach ($diagnostics as $diagnostic) {
            $status = $diagnostic->required ? RuntimeHealthStatus::Unavailable : RuntimeHealthStatus::Degraded;
            if ($status === RuntimeHealthStatus::Unavailable) {
                break;
            }
        }

        return new RuntimeHealthResult($status, $diagnostics);
    }
}
