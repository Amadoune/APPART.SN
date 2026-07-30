<?php

namespace App\Application\AdministrativeActionLifecycleEventConsumption;

use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleDeliveryPayload;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRouter;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleTransportEnvelope;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use Throwable;

final readonly class AdministrativeActionLifecycleDeliveryConsumer implements PublicProjectionDeliveryConsumer
{
    public function __construct(
        private AdministrativeActionLifecycleEventRouter $router,
        private AdministrativeActionLifecycleDeliveryConsumptionPolicy $policy,
    ) {}

    public function consume(PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumptionResult
    {
        if (! $message->payload instanceof AdministrativeActionLifecycleDeliveryPayload) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        try {
            $payload = AdministrativeActionLifecycleDeliveryPayload::restore($message->payload->fields());
            $event = $payload->event;
            if ($message->eventType->value !== $event->metadata->eventType->value
                || $message->payloadVersion->value !== $event->metadata->payloadVersion->value
                || $message->sourceModule->value !== 'AdministrationAudit'
                || $message->aggregateType->value !== 'AdministrativeActionLifecycle'
                || $message->aggregateId->value !== $event->payload->actionId->value
                || $message->order->aggregateVersion !== $event->payload->occurredVersion
                || $message->order->eventIndex->value !== 1) {
                return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
            }
            $envelope = AdministrativeActionLifecycleTransportEnvelope::wrap($payload);
        } catch (Throwable) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        return $this->policy->consumptionFor($this->router->route($envelope));
    }
}
