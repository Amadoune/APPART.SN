<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycle;

final readonly class MediaItemLifecycleTransition
{
    public function __construct(
        public MediaItemLifecycleState $from,
        public MediaItemLifecycleState $to,
        public MediaItemLifecycleAction $action,
    ) {}
}
