<?php

namespace App\Application\LeadLifecycleEventRouting;

use App\Application\LeadLifecycleEventTransport\LeadLifecycleDeliveryMetadata;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRouter;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingDiagnostic;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingResult;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleTransportEnvelope;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleTransportSerializer;
use Throwable;

final readonly class DurableLeadLifecycleEventRouter implements LeadLifecycleEventRouter
{
    public function __construct(private LeadLifecycleInboxStore $store) {}

    public function route(LeadLifecycleTransportEnvelope $envelope): LeadLifecycleEventRoutingResult
    {
        try {
            if (! $this->isCoherent($envelope)) {
                return LeadLifecycleEventRoutingResult::rejected(LeadLifecycleEventRoutingDiagnostic::CorruptedEvent);
            }

            return match ($this->store->store($envelope)->status) {
                LeadLifecycleInboxStoreStatus::Stored,
                LeadLifecycleInboxStoreStatus::AlreadyStored => LeadLifecycleEventRoutingResult::routed(),
                LeadLifecycleInboxStoreStatus::Unavailable => LeadLifecycleEventRoutingResult::deferred(),
                LeadLifecycleInboxStoreStatus::RetryableFailure => LeadLifecycleEventRoutingResult::retryableFailure(),
                LeadLifecycleInboxStoreStatus::Rejected => LeadLifecycleEventRoutingResult::rejected(LeadLifecycleEventRoutingDiagnostic::CorruptedEvent),
            };
        } catch (Throwable) {
            return LeadLifecycleEventRoutingResult::retryableFailure();
        }
    }

    private function isCoherent(LeadLifecycleTransportEnvelope $envelope): bool
    {
        $expected = LeadLifecycleTransportEnvelope::wrap($envelope->payload);

        return hash_equals($expected->messageId, $envelope->messageId)
            && $expected->messageType === $envelope->messageType
            && $envelope->transportVersion === LeadLifecycleTransportEnvelope::VERSION
            && $envelope->metadata->source === LeadLifecycleDeliveryMetadata::SOURCE
            && hash_equals($envelope->payload->event->payload->eventId->value, $envelope->metadata->businessEventId)
            && hash_equals($envelope->payload->checksum(), $envelope->metadata->payloadChecksum)
            && (new LeadLifecycleTransportSerializer)->serialize($expected) === (new LeadLifecycleTransportSerializer)->serialize($envelope);
    }
}
