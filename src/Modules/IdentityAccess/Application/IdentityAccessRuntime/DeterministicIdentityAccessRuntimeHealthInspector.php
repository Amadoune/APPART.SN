<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime;

use Appart\Modules\IdentityAccess\Application\IdentityAccessRuntime\Contract\IdentityAccessRuntimeHealthInspector;

final readonly class DeterministicIdentityAccessRuntimeHealthInspector implements IdentityAccessRuntimeHealthInspector
{
    /** @param list<IdentityAccessRuntimeRegistration> $registrations */
    public function __construct(private array $registrations) {}

    public function inspect(): IdentityAccessRuntimeHealthReport
    {
        $unhealthy = [];
        foreach ($this->registrations as $registration) {
            if (! $registration->bound || ! $registration->compatible) {
                $unhealthy[] = $registration->component;
            }
        }

        return new IdentityAccessRuntimeHealthReport(
            $unhealthy === []
                ? IdentityAccessRuntimeHealthStatus::Healthy
                : IdentityAccessRuntimeHealthStatus::Unhealthy,
            $unhealthy,
        );
    }
}
