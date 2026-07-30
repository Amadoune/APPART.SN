<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleDiagnostic;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;

final readonly class ReservationLifecycleOrchestrationResult
{
    private function __construct(
        public ReservationLifecycleOrchestrationStatus $status,
        public ?ReservationLifecycleTransition $transition,
        public ?ReservationLifecycleDiagnostic $diagnostic,
    ) {}

    public static function applied(ReservationLifecycleTransition $transition): self
    {
        return new self(ReservationLifecycleOrchestrationStatus::Applied, $transition, null);
    }

    public static function missing(): self
    {
        return new self(ReservationLifecycleOrchestrationStatus::Missing, null, null);
    }

    public static function versionConflict(): self
    {
        return new self(ReservationLifecycleOrchestrationStatus::VersionConflict, null, null);
    }

    public static function stateConflict(): self
    {
        return new self(ReservationLifecycleOrchestrationStatus::StateConflict, null, null);
    }

    public static function alreadyApplied(ReservationLifecycleTransition $transition): self
    {
        return new self(ReservationLifecycleOrchestrationStatus::AlreadyApplied, $transition, null);
    }

    public static function denied(ReservationLifecycleDiagnostic $diagnostic): self
    {
        return new self(ReservationLifecycleOrchestrationStatus::Denied, null, $diagnostic);
    }

    public static function persistenceCorrupted(): self
    {
        return new self(ReservationLifecycleOrchestrationStatus::PersistenceCorrupted, null, null);
    }
}
