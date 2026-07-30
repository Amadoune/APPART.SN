<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleContext;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use InvalidArgumentException;

final readonly class MediaItemLifecycleContextualAppendInspection
{
    public function __construct(
        public MediaItemLifecycleId $mediaId,
        public int $version,
        public MediaItemLifecycleTransition $transition,
        public MediaItemLifecycleTransitionContext $context,
        public MediaItemLifecycleContextChecksum $checksum,
    ) {
        if ($version < 2 || $version !== $context->expectedVersion->value + 1) {
            throw new InvalidArgumentException('The inspected version must follow the expected lifecycle version.');
        }
        if ($mediaId->value !== $context->mediaId->value) {
            throw new InvalidArgumentException('The inspected and contextual media identities must match.');
        }
    }
}
