<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleContext;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use InvalidArgumentException;

final readonly class MediaItemLifecycleContextualAppend
{
    public function __construct(
        public MediaItemLifecycleId $mediaId,
        public MediaItemLifecycleTransition $transition,
        public MediaItemLifecycleTransitionContext $context,
    ) {
        if ($mediaId->value !== $context->mediaId->value) {
            throw new InvalidArgumentException('The lifecycle and context media identities must match.');
        }
    }

    public function nextVersion(): int
    {
        return $this->context->expectedVersion->value + 1;
    }
}
