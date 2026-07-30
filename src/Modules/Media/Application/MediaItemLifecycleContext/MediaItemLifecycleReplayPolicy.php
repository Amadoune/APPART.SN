<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleContext;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;

final readonly class MediaItemLifecycleReplayPolicy
{
    public function classify(
        MediaItemLifecycleAction $requestedAction,
        MediaItemLifecycleTransitionContext $requestedContext,
        MediaItemLifecycleContextualAppendInspection $inspected,
    ): MediaItemLifecycleReplayOutcome {
        if ($inspected->version !== $requestedContext->expectedVersion->value + 1
            || $inspected->transition->action !== $requestedAction) {
            return MediaItemLifecycleReplayOutcome::Conflict;
        }

        if (! hash_equals($inspected->checksum->value, $requestedContext->checksum()->value)) {
            return MediaItemLifecycleReplayOutcome::ContextDivergence;
        }

        return MediaItemLifecycleReplayOutcome::AlreadyApplied;
    }
}
