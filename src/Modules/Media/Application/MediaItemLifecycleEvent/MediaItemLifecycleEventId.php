<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleEvent;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use InvalidArgumentException;

final readonly class MediaItemLifecycleEventId
{
    private function __construct(public string $value) {}

    public static function derive(
        MediaItemLifecycleEventType $type,
        MediaItemLifecycleEventPayloadVersion $payloadVersion,
        MediaItemLifecycleId $mediaId,
        MediaItemLifecycleTransition $transition,
        int $occurredVersion,
    ): self {
        if ($occurredVersion < 2) {
            throw new InvalidArgumentException('Media item lifecycle event version must be at least two.');
        }

        return new self(hash('sha256', implode("\n", [
            $type->value,
            (string) $payloadVersion->value,
            $mediaId->value,
            $transition->from->value,
            $transition->action->value,
            $transition->to->value,
            (string) $occurredVersion,
        ])));
    }
}
