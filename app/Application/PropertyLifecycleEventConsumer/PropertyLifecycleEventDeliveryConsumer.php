<?php

namespace App\Application\PropertyLifecycleEventConsumer;

use App\Application\PropertyLifecycleEventTransport\Contract\PropertyLifecycleEventRouter;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleDeliveryPayload;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleEventRoutingStatus;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleEventTransportException;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use DateTimeImmutable;
use DateTimeZone;

final readonly class PropertyLifecycleEventDeliveryConsumer implements PublicProjectionDeliveryConsumer
{
    public function __construct(private PropertyLifecycleEventRouter $router) {}

    public function consume(PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumptionResult
    {
        if (! $message->payload instanceof PropertyLifecycleDeliveryPayload) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }
        try {
            $event = PropertyLifecycleDeliveryPayload::restore($message->payload->fields())->event;
        } catch (PropertyLifecycleEventTransportException) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }
        if ($message->eventType->value !== $event->type->value
            || $message->payloadVersion->value !== $event->payloadVersion->value
            || $message->sourceModule->value !== 'RealEstateCatalog'
            || $message->aggregateType->value !== 'Property'
            || $message->aggregateId->value !== $event->payload->propertyId->value
            || $message->order->aggregateVersion !== $event->payload->lifecycleVersion
            || $message->order->eventIndex->value !== 1
            || $this->canonicalUtc($message->occurredAt) !== $event->metadata->occurredAt->value
            || $this->canonicalUtc($message->recordedAt) !== $event->metadata->recordedAt->value) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        return match ($this->router->route($event)->status) {
            PropertyLifecycleEventRoutingStatus::Routed => PublicProjectionDeliveryConsumptionResult::Consumed,
            PropertyLifecycleEventRoutingStatus::Deferred => PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness,
            PropertyLifecycleEventRoutingStatus::RetryableFailure => PublicProjectionDeliveryConsumptionResult::RetryableFailure,
            PropertyLifecycleEventRoutingStatus::Rejected => PublicProjectionDeliveryConsumptionResult::PermanentFailure,
        };
    }

    private function canonicalUtc(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
