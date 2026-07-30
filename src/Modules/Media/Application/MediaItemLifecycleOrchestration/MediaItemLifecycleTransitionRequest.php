<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleOrchestration;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleTransitionContext;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;

final readonly class MediaItemLifecycleTransitionRequest
{
    public function __construct(
        public MediaItemLifecycleId $mediaId,
        public MediaItemLifecycleAction $action,
        public MediaItemLifecycleTransitionContext $context,
    ) {}
}
