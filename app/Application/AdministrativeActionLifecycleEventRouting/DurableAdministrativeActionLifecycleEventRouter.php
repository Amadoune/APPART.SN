<?php

namespace App\Application\AdministrativeActionLifecycleEventRouting;

use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRouter;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingDiagnostic;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingResult;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleTransportEnvelope;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleTransportSerializer;
use Throwable;

final readonly class DurableAdministrativeActionLifecycleEventRouter implements AdministrativeActionLifecycleEventRouter
{
    public function __construct(private AdministrativeActionLifecycleInboxStore $store) {}

    public function route(AdministrativeActionLifecycleTransportEnvelope $envelope): AdministrativeActionLifecycleEventRoutingResult
    {
        try {
            if (! $this->isCoherent($envelope)) {
                return AdministrativeActionLifecycleEventRoutingResult::rejected(
                    AdministrativeActionLifecycleEventRoutingDiagnostic::CorruptedEvent,
                );
            }

            return match ($this->store->store($envelope)->status) {
                AdministrativeActionLifecycleInboxStoreStatus::Stored,
                AdministrativeActionLifecycleInboxStoreStatus::AlreadyStored => AdministrativeActionLifecycleEventRoutingResult::routed(),
                AdministrativeActionLifecycleInboxStoreStatus::Unavailable => AdministrativeActionLifecycleEventRoutingResult::deferred(),
                AdministrativeActionLifecycleInboxStoreStatus::RetryableFailure => AdministrativeActionLifecycleEventRoutingResult::retryableFailure(),
                AdministrativeActionLifecycleInboxStoreStatus::Rejected => AdministrativeActionLifecycleEventRoutingResult::rejected(
                    AdministrativeActionLifecycleEventRoutingDiagnostic::CorruptedEvent,
                ),
            };
        } catch (Throwable) {
            return AdministrativeActionLifecycleEventRoutingResult::retryableFailure();
        }
    }

    private function isCoherent(AdministrativeActionLifecycleTransportEnvelope $envelope): bool
    {
        $expected = AdministrativeActionLifecycleTransportEnvelope::wrap($envelope->payload);
        $serializer = new AdministrativeActionLifecycleTransportSerializer;

        return hash_equals($expected->messageId, $envelope->messageId)
            && $expected->messageType === $envelope->messageType
            && $envelope->transportVersion === AdministrativeActionLifecycleTransportEnvelope::VERSION
            && $envelope->metadata->source === 'AdministrationAudit'
            && hash_equals($envelope->payload->event->payload->eventId->value, $envelope->metadata->businessEventId)
            && hash_equals($envelope->payload->checksum(), $envelope->metadata->payloadChecksum)
            && $serializer->serialize($expected) === $serializer->serialize($envelope);
    }
}
