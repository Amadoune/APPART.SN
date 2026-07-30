<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycle;

final readonly class PlaceLifecycleWorkflowResult
{
    private function __construct(
        public PlaceLifecycleDecision $decision,
        public PlaceLifecycleState $state,
        public ?PlaceLifecycleTransition $transition,
    ) {}

    public static function applied(PlaceLifecycleTransition $transition): self
    {
        return new self(PlaceLifecycleDecision::Applied, $transition->to, $transition);
    }

    public static function refused(PlaceLifecycleDecision $decision, PlaceLifecycleState $state): self
    {
        return new self($decision, $state, null);
    }
}
