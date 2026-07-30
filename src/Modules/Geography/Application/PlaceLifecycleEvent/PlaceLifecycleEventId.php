<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycleEvent;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use InvalidArgumentException;

final readonly class PlaceLifecycleEventId
{
    private function __construct(public string $value) {}

    public static function derive(
        PlaceLifecycleEventType $type,
        PlaceLifecycleEventPayloadVersion $payloadVersion,
        PlaceId $placeId,
        PlaceLifecycleTransition $transition,
        int $occurredVersion,
        ?PlaceId $targetPlaceId,
    ): self {
        if ($occurredVersion < 2) {
            throw new InvalidArgumentException('A Place Lifecycle event version must be at least two.');
        }

        return new self(hash('sha256', implode("\n", [
            $type->value,
            (string) $payloadVersion->value,
            $placeId->value,
            $transition->from->value,
            $transition->action->value,
            $transition->to->value,
            (string) $occurredVersion,
            $targetPlaceId === null ? '' : $targetPlaceId->value,
        ])));
    }
}
