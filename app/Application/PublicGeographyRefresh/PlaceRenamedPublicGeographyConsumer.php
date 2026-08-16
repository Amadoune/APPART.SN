<?php

namespace App\Application\PublicGeographyRefresh;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;

final readonly class PlaceRenamedPublicGeographyConsumer implements PublicProjectionDeliveryConsumer
{
    public function __construct(private PublicGeographyMutationRefreshConsumer $refresh) {}

    public function consume(PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumptionResult
    {
        if (! $message->payload instanceof PlaceRenamedPublicGeographyPayload
            || $message->eventType->value !== 'place.lifecycle.renamed'
            || $message->sourceModule->value !== 'Geography'
            || $message->aggregateId->value !== $message->payload->placeId
            || $message->order->aggregateVersion !== $message->payload->aggregateVersion) {
            return PublicProjectionDeliveryConsumptionResult::PermanentFailure;
        }

        return $this->refresh->consume($message->payload->placeId, $message->payload->eventId)
            ? PublicProjectionDeliveryConsumptionResult::Consumed
            : PublicProjectionDeliveryConsumptionResult::RetryableFailure;
    }
}
