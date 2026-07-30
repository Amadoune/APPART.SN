<?php

namespace App\Application\ReservationLifecycleEventConsumer;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleDeliveryPayload;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleEventRouterPort;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportEnvelope;
use Throwable;

final readonly class ReservationLifecycleDeliveryConsumer implements PublicProjectionDeliveryConsumer
{
    public function __construct(
        private ReservationLifecycleEventRouterPort $router,
        private ReservationLifecycleDeliveryConsumptionPolicy $policy,
    ) {}

    public function consume(PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumptionResult
    {
        if (! $message->payload instanceof ReservationLifecycleDeliveryPayload) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        try {
            $payload = ReservationLifecycleDeliveryPayload::restore($message->payload->fields());
            $event = $payload->event;
            if ($message->eventType->value !== $event->metadata->eventType->value
                || $message->payloadVersion->value !== $event->metadata->payloadVersion->value
                || $message->sourceModule->value !== 'ReservationLifecycle'
                || $message->aggregateType->value !== 'ReservationLifecycle'
                || $message->aggregateId->value !== $event->payload->reservationId->value
                || $message->order->aggregateVersion !== $event->payload->occurredVersion
                || $message->order->eventIndex->value !== 1) {
                return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
            }

            $envelope = ReservationLifecycleTransportEnvelope::wrap($payload);
        } catch (Throwable) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        return $this->policy->consumptionFor($this->router->route($envelope));
    }
}
