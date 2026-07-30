<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycleEvent;

use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeOccurredAt;
use InvalidArgumentException;

final readonly class PlaceLifecycleEventV1
{
    public function __construct(
        public PlaceLifecycleEventType $type,
        public PlaceLifecycleEventId $eventId,
        public PlaceMergeOccurredAt $occurredAt,
        public PlaceLifecycleEventPayloadV1 $payload,
    ) {
        $certifiedType = (new PlaceLifecycleEventCatalog)->typeFor($payload->transition());
        $derivedId = PlaceLifecycleEventId::derive(
            $type,
            $payload->version,
            $payload->placeId,
            $payload->transition(),
            $payload->occurredVersion,
            $payload->targetPlaceId,
        );

        if (
            $certifiedType !== $type
            || $eventId->value !== $payload->eventId->value
            || $eventId->value !== $derivedId->value
        ) {
            throw new InvalidArgumentException('Inconsistent Place Lifecycle event contract.');
        }
    }

    /**
     * @return array{
     *     eventId: string,
     *     eventType: string,
     *     payloadVersion: int,
     *     occurredAt: string,
     *     payload: array<string, int|string>
     * }
     */
    public function contract(): array
    {
        return [
            'eventId' => $this->eventId->value,
            'eventType' => $this->type->value,
            'payloadVersion' => $this->payload->version->value,
            'occurredAt' => $this->occurredAt->canonical(),
            'payload' => $this->payload->fields(),
        ];
    }
}
