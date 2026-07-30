<?php

namespace App\Application\PlaceLifecycleEventRouting;

use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRouter;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRoutingDiagnostic;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRoutingResult;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleTransportEnvelope;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleTransportSerializer;
use Throwable;

final readonly class DurablePlaceLifecycleEventRouter implements PlaceLifecycleEventRouter
{
    public function __construct(private PlaceLifecycleInboxStore $store) {}

    public function route(PlaceLifecycleTransportEnvelope $envelope): PlaceLifecycleEventRoutingResult
    {
        try {
            if (! $this->isCoherent($envelope)) {
                return PlaceLifecycleEventRoutingResult::rejected(
                    PlaceLifecycleEventRoutingDiagnostic::CorruptedEvent,
                );
            }

            return match ($this->store->store($envelope)->status) {
                PlaceLifecycleInboxStoreStatus::Stored,
                PlaceLifecycleInboxStoreStatus::AlreadyStored => PlaceLifecycleEventRoutingResult::routed(),
                PlaceLifecycleInboxStoreStatus::Unavailable => PlaceLifecycleEventRoutingResult::deferred(),
                PlaceLifecycleInboxStoreStatus::RetryableFailure => PlaceLifecycleEventRoutingResult::retryableFailure(),
                PlaceLifecycleInboxStoreStatus::Rejected => PlaceLifecycleEventRoutingResult::rejected(
                    PlaceLifecycleEventRoutingDiagnostic::CorruptedEvent,
                ),
            };
        } catch (Throwable) {
            return PlaceLifecycleEventRoutingResult::retryableFailure();
        }
    }

    private function isCoherent(PlaceLifecycleTransportEnvelope $envelope): bool
    {
        $expected = PlaceLifecycleTransportEnvelope::wrap($envelope->payload);
        $serializer = new PlaceLifecycleTransportSerializer;

        return hash_equals($expected->messageId->value, $envelope->messageId->value)
            && $expected->messageType === $envelope->messageType
            && $envelope->metadata->source === 'PlaceLifecycle'
            && hash_equals($envelope->payload->event->eventId->value, $envelope->metadata->eventId)
            && hash_equals($envelope->payload->transportChecksum()->value, $envelope->metadata->payloadChecksum->value)
            && $serializer->serialize($expected) === $serializer->serialize($envelope);
    }
}
