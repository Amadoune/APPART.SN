<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleEvent;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use DomainException;

final readonly class MediaItemLifecycleEventCatalog
{
    public function typeFor(MediaItemLifecycleTransition $transition): MediaItemLifecycleEventType
    {
        return match ([$transition->from, $transition->action, $transition->to]) {
            [MediaItemLifecycleState::Active, MediaItemLifecycleAction::Remove, MediaItemLifecycleState::Removed] => MediaItemLifecycleEventType::Removed,
            [MediaItemLifecycleState::Active, MediaItemLifecycleAction::Archive, MediaItemLifecycleState::Archived] => MediaItemLifecycleEventType::Archived,
            default => throw new DomainException('Transition is not certified for a media item lifecycle event.'),
        };
    }
}
