<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract;

use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualAppend;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualWriteResult;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecyclePersistenceReadResult;

interface MediaItemLifecycleContextualTransitionStore
{
    public function read(MediaItemLifecycleId $mediaId): MediaItemLifecyclePersistenceReadResult;

    public function append(MediaItemLifecycleContextualAppend $append): MediaItemLifecycleContextualWriteResult;
}
