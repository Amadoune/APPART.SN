<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle;

final readonly class ReservationLifecycleWorkflow
{
    /** @var array<string, ReservationLifecycleState> */
    private const array TRANSITIONS = [
        'draft>submit' => ReservationLifecycleState::Requested,
        'draft>cancel' => ReservationLifecycleState::Cancelled,
        'requested>confirm' => ReservationLifecycleState::Confirmed,
        'requested>reject' => ReservationLifecycleState::Rejected,
        'requested>cancel' => ReservationLifecycleState::Cancelled,
        'requested>expire' => ReservationLifecycleState::Expired,
        'confirmed>start' => ReservationLifecycleState::InProgress,
        'confirmed>cancel' => ReservationLifecycleState::Cancelled,
        'confirmed>expire' => ReservationLifecycleState::Expired,
        'in_progress>complete' => ReservationLifecycleState::Completed,
        'in_progress>cancel' => ReservationLifecycleState::Cancelled,
    ];

    public function decide(ReservationLifecycleState $state, ReservationLifecycleAction $action): ReservationLifecycleDecision
    {
        if ($action === ReservationLifecycleAction::Unknown) {
            return ReservationLifecycleDecision::denied(ReservationLifecycleDiagnostic::unknownAction());
        }
        if ($this->isTerminal($state)) {
            return ReservationLifecycleDecision::denied(ReservationLifecycleDiagnostic::terminalState());
        }

        $target = self::TRANSITIONS[$state->value.'>'.$action->value] ?? null;
        if ($target !== null) {
            return ReservationLifecycleDecision::allowed(new ReservationLifecycleTransition($state, $target, $action));
        }
        if ($this->nominalTarget($action) === $state) {
            return ReservationLifecycleDecision::denied(ReservationLifecycleDiagnostic::incompatibleState());
        }

        return ReservationLifecycleDecision::denied(ReservationLifecycleDiagnostic::transitionForbidden());
    }

    private function isTerminal(ReservationLifecycleState $state): bool
    {
        return match ($state) {
            ReservationLifecycleState::Completed,
            ReservationLifecycleState::Cancelled,
            ReservationLifecycleState::Expired,
            ReservationLifecycleState::Rejected => true,
            ReservationLifecycleState::Draft,
            ReservationLifecycleState::Requested,
            ReservationLifecycleState::Confirmed,
            ReservationLifecycleState::InProgress => false,
        };
    }

    private function nominalTarget(ReservationLifecycleAction $action): ReservationLifecycleState
    {
        return match ($action) {
            ReservationLifecycleAction::Submit => ReservationLifecycleState::Requested,
            ReservationLifecycleAction::Confirm => ReservationLifecycleState::Confirmed,
            ReservationLifecycleAction::Reject => ReservationLifecycleState::Rejected,
            ReservationLifecycleAction::Start => ReservationLifecycleState::InProgress,
            ReservationLifecycleAction::Complete => ReservationLifecycleState::Completed,
            ReservationLifecycleAction::Cancel => ReservationLifecycleState::Cancelled,
            ReservationLifecycleAction::Expire => ReservationLifecycleState::Expired,
            ReservationLifecycleAction::Unknown => throw new \LogicException('Unknown action has no nominal target.'),
        };
    }
}
