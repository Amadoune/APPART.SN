<?php

namespace App\Application\MediaItemLifecycleEventIntegration;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleTransitionContext;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\MediaItemLifecycleTransitionRequest;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;

final readonly class MediaItemLifecycleAtomicEventRequest
{
    public function __construct(
        public MediaItemLifecycleId $mediaId,
        public MediaItemLifecycleAction $action,
        public MediaItemLifecycleTransitionContext $context,
        public MediaItemLifecycleOccurredAt $recordedAt,
    ) {}

    public function transitionRequest(): MediaItemLifecycleTransitionRequest
    {
        return new MediaItemLifecycleTransitionRequest($this->mediaId, $this->action, $this->context);
    }
}
