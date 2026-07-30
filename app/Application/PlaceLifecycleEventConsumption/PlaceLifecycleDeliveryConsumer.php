<?php

namespace App\Application\PlaceLifecycleEventConsumption;

use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleDeliveryPayload;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRouter;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleTransportEnvelope;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use Throwable;

final readonly class PlaceLifecycleDeliveryConsumer implements PublicProjectionDeliveryConsumer
{
    public function __construct(
        private PlaceLifecycleEventRouter $router,
        private PlaceLifecycleDeliveryConsumptionPolicy $policy,
    ) {}

    public function consume(PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumptionResult
    {
        if (! $message->payload instanceof PlaceLifecycleDeliveryPayload) {
            return PublicProjectionDeliveryConsumptionResult::PermanentFailure;
        }

        try {
            $payload = PlaceLifecycleDeliveryPayload::restore($message->payload->fields());
            $event = $payload->event;
            if ($message->eventType->value !== $event->type->value
                || $message->payloadVersion->value !== $event->payload->version->value
                || $message->sourceModule->value !== 'Geography'
                || $message->aggregateType->value !== 'PlaceLifecycle'
                || $message->aggregateId->value !== $event->payload->placeId->value
                || $message->order->aggregateVersion !== $event->payload->occurredVersion
                || $message->order->eventIndex->value !== 1) {
                return PublicProjectionDeliveryConsumptionResult::PermanentFailure;
            }
            $envelope = PlaceLifecycleTransportEnvelope::wrap($payload);
        } catch (Throwable) {
            return PublicProjectionDeliveryConsumptionResult::PermanentFailure;
        }

        return match ($this->consumeEnvelope($envelope)) {
            PlaceLifecycleDeliveryConsumptionResult::Acknowledged => PublicProjectionDeliveryConsumptionResult::Consumed,
            PlaceLifecycleDeliveryConsumptionResult::Retry => PublicProjectionDeliveryConsumptionResult::RetryableFailure,
            PlaceLifecycleDeliveryConsumptionResult::Quarantined => PublicProjectionDeliveryConsumptionResult::PermanentFailure,
        };
    }

    public function consumeEnvelope(
        PlaceLifecycleTransportEnvelope $envelope,
    ): PlaceLifecycleDeliveryConsumptionResult {
        try {
            return $this->policy->consumptionFor($this->router->route($envelope)->status);
        } catch (Throwable) {
            return PlaceLifecycleDeliveryConsumptionResult::Retry;
        }
    }
}
