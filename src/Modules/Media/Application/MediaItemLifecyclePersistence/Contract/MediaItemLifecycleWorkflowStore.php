<?php

namespace Appart\Modules\Media\Application\MediaItemLifecyclePersistence\Contract;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecyclePersistenceReadResult;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecyclePersistenceWriteResult;

interface MediaItemLifecycleWorkflowStore
{
    public function initialize(MediaItemLifecycleId $mediaId): MediaItemLifecyclePersistenceWriteResult;

    public function append(MediaItemLifecycleId $mediaId, MediaItemLifecycleTransition $transition, int $version): MediaItemLifecyclePersistenceWriteResult;

    public function read(MediaItemLifecycleId $mediaId): MediaItemLifecyclePersistenceReadResult;
}
