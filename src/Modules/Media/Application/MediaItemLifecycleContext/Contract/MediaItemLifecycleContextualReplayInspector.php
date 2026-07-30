<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract;

use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualInspectionResult;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;

interface MediaItemLifecycleContextualReplayInspector
{
    public function inspectLatest(MediaItemLifecycleId $mediaId): MediaItemLifecycleContextualInspectionResult;
}
