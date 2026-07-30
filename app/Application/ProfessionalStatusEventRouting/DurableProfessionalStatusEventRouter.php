<?php

namespace App\Application\ProfessionalStatusEventRouting;

use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusDeliveryMetadata;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRouter;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingDiagnostic;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingResult;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportEnvelope;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportSerializer;
use Throwable;

final readonly class DurableProfessionalStatusEventRouter implements ProfessionalStatusEventRouter
{
    public function __construct(private ProfessionalStatusInboxStore $store) {}

    public function route(ProfessionalStatusTransportEnvelope $envelope): ProfessionalStatusEventRoutingResult
    {
        try {
            if (! $this->isCoherent($envelope)) {
                return ProfessionalStatusEventRoutingResult::rejected(ProfessionalStatusEventRoutingDiagnostic::CorruptedEvent);
            }

            return match ($this->store->store($envelope)->status) {
                ProfessionalStatusInboxStoreStatus::Stored,
                ProfessionalStatusInboxStoreStatus::AlreadyStored => ProfessionalStatusEventRoutingResult::routed(),
                ProfessionalStatusInboxStoreStatus::Unavailable => ProfessionalStatusEventRoutingResult::deferred(),
                ProfessionalStatusInboxStoreStatus::RetryableFailure => ProfessionalStatusEventRoutingResult::retryableFailure(),
                ProfessionalStatusInboxStoreStatus::Rejected => ProfessionalStatusEventRoutingResult::rejected(ProfessionalStatusEventRoutingDiagnostic::CorruptedEvent),
            };
        } catch (Throwable) {
            return ProfessionalStatusEventRoutingResult::retryableFailure();
        }
    }

    private function isCoherent(ProfessionalStatusTransportEnvelope $envelope): bool
    {
        $expected = ProfessionalStatusTransportEnvelope::wrap($envelope->payload);

        return hash_equals($expected->messageId, $envelope->messageId)
            && $expected->messageType === $envelope->messageType
            && $envelope->transportVersion === ProfessionalStatusTransportEnvelope::VERSION
            && $envelope->metadata->source === ProfessionalStatusDeliveryMetadata::SOURCE
            && hash_equals($envelope->payload->event->payload->eventId->value, $envelope->metadata->businessEventId)
            && hash_equals($envelope->payload->checksum(), $envelope->metadata->payloadChecksum)
            && (new ProfessionalStatusTransportSerializer)->serialize($expected) === (new ProfessionalStatusTransportSerializer)->serialize($envelope);
    }
}
