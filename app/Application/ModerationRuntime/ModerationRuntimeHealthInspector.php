<?php

namespace App\Application\ModerationRuntime;

use App\Application\ModerationRuntime\Contract\ModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\Contract\ModerationRuntimeV1;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\DeterministicRuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthComponent;
use App\Application\RuntimeHealth\RuntimeHealthRegistration;
use App\Application\RuntimeHealth\RuntimeHealthRequirement;
use App\Application\RuntimeHealth\RuntimeHealthResult;
use App\Application\RuntimeHealth\RuntimeHealthStatus;

final readonly class ModerationRuntimeHealthInspector implements RuntimeHealthInspector
{
    public function __construct(
        private RuntimeHealthInspector $baseline,
        private bool $runtimeRegistered,
        private ?ModerationRuntimeV1 $runtime,
        private bool $queueRegistered,
        private ?ModerationQueueRuntimeV1 $queue,
    ) {}

    public function inspect(): RuntimeHealthResult
    {
        $baseline = $this->baseline->inspect();
        $extension = (new DeterministicRuntimeHealthInspector(
            [
                new RuntimeHealthRequirement(RuntimeHealthComponent::ModerationRuntime, ModerationRuntimeV1::class),
                new RuntimeHealthRequirement(RuntimeHealthComponent::ModerationQueue, ModerationQueueRuntimeV1::class),
            ],
            [
                new RuntimeHealthRegistration(RuntimeHealthComponent::ModerationRuntime, $this->runtimeRegistered, $this->runtime),
                new RuntimeHealthRegistration(RuntimeHealthComponent::ModerationQueue, $this->queueRegistered, $this->queue),
            ],
        ))->inspect();

        return new RuntimeHealthResult(
            $this->status($baseline->status, $extension->status),
            [...$baseline->diagnostics, ...$extension->diagnostics],
        );
    }

    private function status(RuntimeHealthStatus $baseline, RuntimeHealthStatus $extension): RuntimeHealthStatus
    {
        if ($baseline === RuntimeHealthStatus::Unavailable || $extension === RuntimeHealthStatus::Unavailable) {
            return RuntimeHealthStatus::Unavailable;
        }

        return $baseline === RuntimeHealthStatus::Degraded || $extension === RuntimeHealthStatus::Degraded
            ? RuntimeHealthStatus::Degraded
            : RuntimeHealthStatus::Healthy;
    }
}
