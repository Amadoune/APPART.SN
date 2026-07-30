<?php

namespace App\Application\ListingPublicationEventConsumer;

use App\Application\ListingPublicationEventTransport\Contract\ListingPublicationEventRouter;
use App\Application\ListingPublicationEventTransport\ListingPublicationDeliveryPayload;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventRoutingStatus;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventTransportException;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use DateTimeImmutable;
use DateTimeZone;

final readonly class ListingPublicationEventDeliveryConsumer implements PublicProjectionDeliveryConsumer
{
    public function __construct(private ListingPublicationEventRouter $router) {}

    public function consume(PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumptionResult
    {
        if (! $message->payload instanceof ListingPublicationDeliveryPayload) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }
        try {
            $event = ListingPublicationDeliveryPayload::restore($message->payload->fields())->event;
        } catch (ListingPublicationEventTransportException) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }
        if ($message->eventType->value !== $event->type->value
            || $message->payloadVersion->value !== $event->payloadVersion->value
            || $message->sourceModule->value !== 'ListingLifecycle'
            || $message->aggregateType->value !== 'Listing'
            || $message->aggregateId->value !== $event->payload->listingId->value
            || $message->order->aggregateVersion !== $event->payload->publicationVersion
            || $message->order->eventIndex->value !== 1
            || $this->canonicalUtc($message->occurredAt) !== $event->metadata->occurredAt->value
            || $this->canonicalUtc($message->recordedAt) !== $event->metadata->recordedAt->value) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        return match ($this->router->route($event)->status) {
            ListingPublicationEventRoutingStatus::Routed => PublicProjectionDeliveryConsumptionResult::Consumed,
            ListingPublicationEventRoutingStatus::Deferred => PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness,
            ListingPublicationEventRoutingStatus::RetryableFailure => PublicProjectionDeliveryConsumptionResult::RetryableFailure,
            ListingPublicationEventRoutingStatus::Rejected => PublicProjectionDeliveryConsumptionResult::PermanentFailure,
        };
    }

    private function canonicalUtc(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
