<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

final readonly class PropertyLifecycleWorkflow
{
    /** @var array<string, PropertyLifecycleState> */
    private const array TRANSITIONS = [
        'draft>activate' => PropertyLifecycleState::Active,
        'draft>archive' => PropertyLifecycleState::Archived,
        'active>begin_maintenance' => PropertyLifecycleState::UnderMaintenance,
        'active>mark_unavailable' => PropertyLifecycleState::Unavailable,
        'active>decommission' => PropertyLifecycleState::Decommissioned,
        'under_maintenance>complete_maintenance' => PropertyLifecycleState::Active,
        'under_maintenance>mark_unavailable' => PropertyLifecycleState::Unavailable,
        'under_maintenance>decommission' => PropertyLifecycleState::Decommissioned,
        'unavailable>restore_availability' => PropertyLifecycleState::Active,
        'unavailable>begin_maintenance' => PropertyLifecycleState::UnderMaintenance,
        'unavailable>decommission' => PropertyLifecycleState::Decommissioned,
        'decommissioned>archive' => PropertyLifecycleState::Archived,
    ];

    public function decide(PropertyLifecycleState $state, PropertyLifecycleAction $action): PropertyLifecycleDecision
    {
        if ($action === PropertyLifecycleAction::Unknown) {
            return PropertyLifecycleDecision::denied(PropertyLifecycleDiagnostic::unknownAction());
        }
        if ($state === PropertyLifecycleState::Archived) {
            return PropertyLifecycleDecision::denied(PropertyLifecycleDiagnostic::terminalState());
        }

        $target = self::TRANSITIONS[$state->value.'>'.$action->value] ?? null;
        if ($target !== null) {
            return PropertyLifecycleDecision::allowed(new PropertyLifecycleTransition($state, $target, $action));
        }
        if ($this->nominalTarget($action) === $state) {
            return PropertyLifecycleDecision::denied(PropertyLifecycleDiagnostic::incompatibleState());
        }

        return PropertyLifecycleDecision::denied(PropertyLifecycleDiagnostic::transitionForbidden());
    }

    private function nominalTarget(PropertyLifecycleAction $action): PropertyLifecycleState
    {
        return match ($action) {
            PropertyLifecycleAction::Activate,
            PropertyLifecycleAction::CompleteMaintenance,
            PropertyLifecycleAction::RestoreAvailability => PropertyLifecycleState::Active,
            PropertyLifecycleAction::BeginMaintenance => PropertyLifecycleState::UnderMaintenance,
            PropertyLifecycleAction::MarkUnavailable => PropertyLifecycleState::Unavailable,
            PropertyLifecycleAction::Decommission => PropertyLifecycleState::Decommissioned,
            PropertyLifecycleAction::Archive => PropertyLifecycleState::Archived,
            PropertyLifecycleAction::Unknown => throw new \LogicException('Unknown action has no nominal target.'),
        };
    }
}
