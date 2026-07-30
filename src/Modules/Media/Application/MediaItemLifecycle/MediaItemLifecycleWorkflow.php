<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycle;

final readonly class MediaItemLifecycleWorkflow
{
    public function initialState(): MediaItemLifecycleState
    {
        return MediaItemLifecycleState::Active;
    }

    public function decide(MediaItemLifecycleState $state, MediaItemLifecycleAction $action): MediaItemLifecycleDecision
    {
        return match ($state->value.'>'.$action->value) {
            'active>remove' => $this->allowed($state, MediaItemLifecycleState::Removed, $action),
            'active>archive' => $this->allowed($state, MediaItemLifecycleState::Archived, $action),
            'removed>remove', 'removed>archive', 'archived>remove', 'archived>archive' => MediaItemLifecycleDecision::denied(MediaItemLifecycleDiagnostic::TerminalState),
            'active>unknown', 'removed>unknown', 'archived>unknown' => MediaItemLifecycleDecision::denied(MediaItemLifecycleDiagnostic::UnknownAction),
        };
    }

    private function allowed(MediaItemLifecycleState $from, MediaItemLifecycleState $to, MediaItemLifecycleAction $action): MediaItemLifecycleDecision
    {
        return MediaItemLifecycleDecision::allowed(new MediaItemLifecycleTransition($from, $to, $action));
    }
}
