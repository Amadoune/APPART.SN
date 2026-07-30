<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycle;

final readonly class MediaItemLifecycleDecision
{
    private function __construct(
        public MediaItemLifecycleWorkflowResult $result,
        public ?MediaItemLifecycleTransition $transition,
        public ?MediaItemLifecycleDiagnostic $diagnostic,
    ) {}

    public static function allowed(MediaItemLifecycleTransition $transition): self
    {
        return new self(MediaItemLifecycleWorkflowResult::Allowed, $transition, null);
    }

    public static function denied(MediaItemLifecycleDiagnostic $diagnostic): self
    {
        return new self(MediaItemLifecycleWorkflowResult::Denied, null, $diagnostic);
    }
}
