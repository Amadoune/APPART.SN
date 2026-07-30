<?php

namespace App\Application\ReservationLifecycleEventRouting;

use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleDeliveryMetadata;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleEventRouterPort;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingResult;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingStatus;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportEnvelope;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportSerializer;
use Throwable;

final readonly class DeterministicReservationLifecycleEventRouter implements ReservationLifecycleEventRouterPort
{
    public function __construct(private ReservationLifecycleInboxStore $store) {}

    public function route(ReservationLifecycleTransportEnvelope $envelope): ReservationLifecycleRoutingResult
    {
        try {
            if (! $this->isContractuallyCoherent($envelope)) {
                return new ReservationLifecycleRoutingResult(ReservationLifecycleRoutingStatus::CorruptedEnvelope);
            }

            return $this->store->store($envelope);
        } catch (Throwable) {
            return new ReservationLifecycleRoutingResult(ReservationLifecycleRoutingStatus::PersistenceCorrupted);
        }
    }

    private function isContractuallyCoherent(ReservationLifecycleTransportEnvelope $envelope): bool
    {
        $canonicalEvent = $envelope->payload->fields()['canonicalEvent'];
        $expected = ReservationLifecycleTransportEnvelope::wrap($envelope->payload);

        return hash_equals($expected->messageId, $envelope->messageId)
            && $expected->messageType === $envelope->messageType
            && $envelope->transportVersion === ReservationLifecycleTransportEnvelope::VERSION
            && $envelope->metadata->source === ReservationLifecycleDeliveryMetadata::SOURCE
            && hash_equals($envelope->payload->event->payload->eventId->value, $envelope->metadata->businessEventId)
            && hash_equals(hash('sha256', $canonicalEvent), $envelope->metadata->payloadChecksum)
            && (new ReservationLifecycleTransportSerializer)->serialize($expected)
                === (new ReservationLifecycleTransportSerializer)->serialize($envelope);
    }
}
