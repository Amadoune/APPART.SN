<?php

namespace App\Application\ProfessionalStatusEventConsumption;

use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusDeliveryPayload;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRouter;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportEnvelope;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use Throwable;

final readonly class ProfessionalStatusDeliveryConsumer implements PublicProjectionDeliveryConsumer
{
    public function __construct(private ProfessionalStatusEventRouter $router, private ProfessionalStatusDeliveryConsumptionPolicy $policy) {}

    public function consume(PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumptionResult
    {
        if (! $message->payload instanceof ProfessionalStatusDeliveryPayload) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        try {
            $payload = ProfessionalStatusDeliveryPayload::restore($message->payload->fields());
            $event = $payload->event;
            if ($message->eventType->value !== $event->metadata->eventType->value
                || $message->payloadVersion->value !== $event->metadata->payloadVersion->value
                || $message->sourceModule->value !== 'Professionals'
                || $message->aggregateType->value !== 'ProfessionalStatus'
                || $message->aggregateId->value !== $event->payload->professionalId->value
                || $message->order->aggregateVersion !== $event->payload->occurredVersion
                || $message->order->eventIndex->value !== 1) {
                return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
            }
            $envelope = ProfessionalStatusTransportEnvelope::wrap($payload);
        } catch (Throwable) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        return $this->policy->consumptionFor($this->router->route($envelope));
    }
}
