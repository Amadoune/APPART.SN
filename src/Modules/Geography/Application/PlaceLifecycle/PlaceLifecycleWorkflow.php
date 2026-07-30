<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycle;

use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedState;

final readonly class PlaceLifecycleWorkflow
{
    public function initialState(): PlaceLifecycleState
    {
        return PlaceLifecycleState::Enabled;
    }

    public function decide(
        PlaceLifecycleCurrentState $current,
        PlaceLifecycleAction $action,
        PlaceMergeContextV1 $context,
    ): PlaceLifecycleWorkflowResult {
        if (! $current->placeId->equals($context->sourceId)) {
            return PlaceLifecycleWorkflowResult::refused(PlaceLifecycleDecision::InvalidContext, $current->state);
        }

        if ($current->state === PlaceLifecycleState::Merged) {
            return PlaceLifecycleWorkflowResult::refused(PlaceLifecycleDecision::TerminalState, $current->state);
        }

        return match ($action) {
            PlaceLifecycleAction::Enable => $this->enable($current->state),
            PlaceLifecycleAction::Disable => $this->disable($current->state),
            PlaceLifecycleAction::Merge => $this->merge($current->state, $context),
        };
    }

    private function enable(PlaceLifecycleState $state): PlaceLifecycleWorkflowResult
    {
        return match ($state) {
            PlaceLifecycleState::Enabled => PlaceLifecycleWorkflowResult::refused(PlaceLifecycleDecision::AlreadyInState, $state),
            PlaceLifecycleState::Disabled => PlaceLifecycleWorkflowResult::applied(
                new PlaceLifecycleTransition($state, PlaceLifecycleAction::Enable, PlaceLifecycleState::Enabled),
            ),
            PlaceLifecycleState::Merged => PlaceLifecycleWorkflowResult::refused(PlaceLifecycleDecision::TerminalState, $state),
        };
    }

    private function disable(PlaceLifecycleState $state): PlaceLifecycleWorkflowResult
    {
        return match ($state) {
            PlaceLifecycleState::Enabled => PlaceLifecycleWorkflowResult::applied(
                new PlaceLifecycleTransition($state, PlaceLifecycleAction::Disable, PlaceLifecycleState::Disabled),
            ),
            PlaceLifecycleState::Disabled => PlaceLifecycleWorkflowResult::refused(PlaceLifecycleDecision::AlreadyInState, $state),
            PlaceLifecycleState::Merged => PlaceLifecycleWorkflowResult::refused(PlaceLifecycleDecision::TerminalState, $state),
        };
    }

    private function merge(
        PlaceLifecycleState $state,
        PlaceMergeContextV1 $context,
    ): PlaceLifecycleWorkflowResult {
        if ($context->sourceId->equals($context->targetId)) {
            return PlaceLifecycleWorkflowResult::refused(PlaceLifecycleDecision::SameIdentity, $state);
        }

        if ($context->observedTargetState === PlaceMergeObservedState::Disabled) {
            return PlaceLifecycleWorkflowResult::refused(PlaceLifecycleDecision::TargetDisabled, $state);
        }

        if ($context->observedTargetState === PlaceMergeObservedState::Merged) {
            return PlaceLifecycleWorkflowResult::refused(PlaceLifecycleDecision::TargetMerged, $state);
        }

        if ($context->observedSourceType !== $context->observedTargetType) {
            return PlaceLifecycleWorkflowResult::refused(PlaceLifecycleDecision::DifferentType, $state);
        }

        if ($context->observedSourceCountry->value !== $context->observedTargetCountry->value) {
            return PlaceLifecycleWorkflowResult::refused(PlaceLifecycleDecision::DifferentCountry, $state);
        }

        return PlaceLifecycleWorkflowResult::applied(
            new PlaceLifecycleTransition($state, PlaceLifecycleAction::Merge, PlaceLifecycleState::Merged),
        );
    }
}
