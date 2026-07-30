<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycleEvent;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;
use DomainException;
use InvalidArgumentException;

final readonly class PlaceLifecycleEventCatalog
{
    public function typeFor(PlaceLifecycleTransition $transition): PlaceLifecycleEventType
    {
        return match ([$transition->from, $transition->action, $transition->to]) {
            [PlaceLifecycleState::Disabled, PlaceLifecycleAction::Enable, PlaceLifecycleState::Enabled] => PlaceLifecycleEventType::Enabled,
            [PlaceLifecycleState::Enabled, PlaceLifecycleAction::Disable, PlaceLifecycleState::Disabled] => PlaceLifecycleEventType::Disabled,
            [PlaceLifecycleState::Enabled, PlaceLifecycleAction::Merge, PlaceLifecycleState::Merged],
            [PlaceLifecycleState::Disabled, PlaceLifecycleAction::Merge, PlaceLifecycleState::Merged] => PlaceLifecycleEventType::Merged,
            default => throw new DomainException('Transition is not certified for a Place Lifecycle event.'),
        };
    }

    public function eventFor(
        PlaceLifecycleTransition $transition,
        PlaceMergeContextV1 $context,
        int $occurredVersion,
    ): PlaceLifecycleEventV1 {
        if ($occurredVersion !== $context->expectedSourceVersion->value + 1) {
            throw new InvalidArgumentException('The event evidence does not match its resulting source version.');
        }

        $type = $this->typeFor($transition);
        $targetPlaceId = $transition->action === PlaceLifecycleAction::Merge ? $context->targetId : null;
        $eventId = PlaceLifecycleEventId::derive(
            $type,
            PlaceLifecycleEventPayloadVersion::V1,
            $context->sourceId,
            $transition,
            $occurredVersion,
            $targetPlaceId,
        );
        $payload = new PlaceLifecycleEventPayloadV1(
            $eventId,
            $context->sourceId,
            $transition->from,
            $transition->action,
            $transition->to,
            $occurredVersion,
            $targetPlaceId,
        );

        return new PlaceLifecycleEventV1($type, $eventId, $context->occurredAt, $payload);
    }
}
