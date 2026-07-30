<?php

namespace App\Application\LeadLifecycleEventConsumption;

use App\Application\LeadLifecycleEventTransport\LeadLifecycleDeliveryPayload;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRouter;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleTransportEnvelope;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use Throwable;

final readonly class LeadLifecycleDeliveryConsumer implements PublicProjectionDeliveryConsumer
{
    public function __construct(
        private LeadLifecycleEventRouter $router,
        private LeadLifecycleDeliveryConsumptionPolicy $policy,
    ) {}

    public function consume(PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumptionResult
    {
        if (! $message->payload instanceof LeadLifecycleDeliveryPayload) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        try {
            $payload = LeadLifecycleDeliveryPayload::restore($message->payload->fields());
            $event = $payload->event;
            if ($message->eventType->value !== $event->metadata->eventType->value
                || $message->payloadVersion->value !== $event->metadata->payloadVersion->value
                || $message->sourceModule->value !== 'ContactsLeads'
                || $message->aggregateType->value !== 'LeadLifecycle'
                || $message->aggregateId->value !== $event->payload->leadId->value
                || $message->order->aggregateVersion !== $event->payload->occurredVersion
                || $message->order->eventIndex->value !== 1) {
                return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
            }

            $envelope = LeadLifecycleTransportEnvelope::wrap($payload);
        } catch (Throwable) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        return $this->policy->consumptionFor($this->router->route($envelope));
    }
}
