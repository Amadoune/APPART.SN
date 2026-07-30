<?php

namespace App\Application\MediaItemLifecycleEventRouting;

use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleDeliveryMetadata;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRouter;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingDiagnostic;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingResult;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportEnvelope;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportSerializer;
use Throwable;

final readonly class DurableMediaItemLifecycleEventRouter implements MediaItemLifecycleEventRouter
{
    public function __construct(private MediaItemLifecycleInboxStore $store) {}

    public function route(MediaItemLifecycleTransportEnvelope $envelope): MediaItemLifecycleEventRoutingResult
    {
        try {
            if (! $this->isCoherent($envelope)) {
                return MediaItemLifecycleEventRoutingResult::rejected(MediaItemLifecycleEventRoutingDiagnostic::CorruptedEvent);
            }

            return match ($this->store->store($envelope)->status) {
                MediaItemLifecycleInboxStoreStatus::Stored,
                MediaItemLifecycleInboxStoreStatus::AlreadyStored => MediaItemLifecycleEventRoutingResult::routed(),
                MediaItemLifecycleInboxStoreStatus::Unavailable => MediaItemLifecycleEventRoutingResult::deferred(),
                MediaItemLifecycleInboxStoreStatus::RetryableFailure => MediaItemLifecycleEventRoutingResult::retryableFailure(),
                MediaItemLifecycleInboxStoreStatus::Rejected => MediaItemLifecycleEventRoutingResult::rejected(MediaItemLifecycleEventRoutingDiagnostic::CorruptedEvent),
            };
        } catch (Throwable) {
            return MediaItemLifecycleEventRoutingResult::retryableFailure();
        }
    }

    private function isCoherent(MediaItemLifecycleTransportEnvelope $envelope): bool
    {
        $expected = MediaItemLifecycleTransportEnvelope::wrap($envelope->payload);

        return hash_equals($expected->messageId, $envelope->messageId)
            && $expected->messageType === $envelope->messageType
            && $envelope->transportVersion === MediaItemLifecycleTransportEnvelope::VERSION
            && $envelope->metadata->source === MediaItemLifecycleDeliveryMetadata::SOURCE
            && hash_equals($envelope->payload->event->payload->eventId->value, $envelope->metadata->businessEventId)
            && hash_equals($envelope->payload->checksum(), $envelope->metadata->payloadChecksum)
            && (new MediaItemLifecycleTransportSerializer)->serialize($expected) === (new MediaItemLifecycleTransportSerializer)->serialize($envelope);
    }
}
