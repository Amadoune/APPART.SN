<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleWorkflow;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleWorkflowResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\Contract\ReservationLifecycleEventOrchestrator;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\Contract\ReservationLifecycleWorkflowStore;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationLifecyclePersistenceReadStatus;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationLifecyclePersistenceWriteResult;
use Throwable;

final readonly class DeterministicReservationLifecycleEventOrchestrator implements ReservationLifecycleEventOrchestrator
{
    public function __construct(
        private ReservationLifecycleWorkflow $workflow,
        private ReservationLifecycleWorkflowStore $store,
    ) {}

    public function transition(ReservationLifecycleOrchestrationRequest $request): ReservationLifecycleOrchestrationResult
    {
        try {
            $current = $this->store->read($request->reservationId);
        } catch (Throwable) {
            return ReservationLifecycleOrchestrationResult::persistenceCorrupted();
        }

        if ($current->status === ReservationLifecyclePersistenceReadStatus::Missing) {
            return ReservationLifecycleOrchestrationResult::missing();
        }
        if ($current->status === ReservationLifecyclePersistenceReadStatus::Corrupted || $current->snapshot === null) {
            return ReservationLifecycleOrchestrationResult::persistenceCorrupted();
        }
        if ($current->snapshot->version !== $request->expectedVersion) {
            return ReservationLifecycleOrchestrationResult::versionConflict();
        }

        $decision = $this->workflow->decide($current->snapshot->state, $request->action);
        if ($decision->result === ReservationLifecycleWorkflowResult::Denied) {
            return $decision->diagnostic === null
                ? ReservationLifecycleOrchestrationResult::persistenceCorrupted()
                : ReservationLifecycleOrchestrationResult::denied($decision->diagnostic);
        }
        if ($decision->transition === null) {
            return ReservationLifecycleOrchestrationResult::persistenceCorrupted();
        }

        try {
            $persisted = $this->store->append($request->reservationId, $decision->transition, $request->expectedVersion + 1);
        } catch (Throwable) {
            return ReservationLifecycleOrchestrationResult::persistenceCorrupted();
        }

        return match ($persisted) {
            ReservationLifecyclePersistenceWriteResult::Applied => ReservationLifecycleOrchestrationResult::applied($decision->transition),
            ReservationLifecyclePersistenceWriteResult::AlreadyApplied => ReservationLifecycleOrchestrationResult::alreadyApplied($decision->transition),
            ReservationLifecyclePersistenceWriteResult::RejectedVersion => ReservationLifecycleOrchestrationResult::versionConflict(),
            ReservationLifecyclePersistenceWriteResult::StateConflict => ReservationLifecycleOrchestrationResult::stateConflict(),
            ReservationLifecyclePersistenceWriteResult::TransitionRejected => ReservationLifecycleOrchestrationResult::persistenceCorrupted(),
        };
    }
}
