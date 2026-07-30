<?php

namespace App\Application\ProfessionalEndpoint;

use App\Application\ProfessionalEndpoint\Contract\ProfessionalEndpointRuntimeV1;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\DeterministicRuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthComponent;
use App\Application\RuntimeHealth\RuntimeHealthRegistration;
use App\Application\RuntimeHealth\RuntimeHealthRequirement;
use App\Application\RuntimeHealth\RuntimeHealthResult;
use App\Application\RuntimeHealth\RuntimeHealthStatus;

final readonly class ProfessionalEndpointRuntimeHealthInspector implements RuntimeHealthInspector
{
    public function __construct(
        private RuntimeHealthInspector $baseline,
        private bool $registered,
        private ?ProfessionalEndpointRuntimeV1 $endpoint,
    ) {}

    public function inspect(): RuntimeHealthResult
    {
        $baseline = $this->baseline->inspect();
        $endpoint = (new DeterministicRuntimeHealthInspector(
            [new RuntimeHealthRequirement(
                RuntimeHealthComponent::ProfessionalEndpoint,
                ProfessionalEndpointRuntimeV1::class,
            )],
            [new RuntimeHealthRegistration(
                RuntimeHealthComponent::ProfessionalEndpoint,
                $this->registered,
                $this->endpoint,
            )],
        ))->inspect();

        return new RuntimeHealthResult(
            $this->status($baseline->status, $endpoint->status),
            [...$baseline->diagnostics, ...$endpoint->diagnostics],
        );
    }

    private function status(RuntimeHealthStatus $baseline, RuntimeHealthStatus $endpoint): RuntimeHealthStatus
    {
        if ($baseline === RuntimeHealthStatus::Unavailable || $endpoint === RuntimeHealthStatus::Unavailable) {
            return RuntimeHealthStatus::Unavailable;
        }

        return $baseline === RuntimeHealthStatus::Degraded || $endpoint === RuntimeHealthStatus::Degraded
            ? RuntimeHealthStatus::Degraded
            : RuntimeHealthStatus::Healthy;
    }
}
