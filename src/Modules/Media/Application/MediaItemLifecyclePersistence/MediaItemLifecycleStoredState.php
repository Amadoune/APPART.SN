<?php

namespace Appart\Modules\Media\Application\MediaItemLifecyclePersistence;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use InvalidArgumentException;

final readonly class MediaItemLifecycleStoredState
{
    public function __construct(
        public MediaItemLifecycleId $mediaId,
        public MediaItemLifecycleState $state,
        public int $version,
    ) {
        if ($version < 1) {
            throw new InvalidArgumentException('Media item lifecycle version must be positive.');
        }
    }
}
