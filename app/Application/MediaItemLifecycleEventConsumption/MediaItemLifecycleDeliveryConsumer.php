<?php

namespace App\Application\MediaItemLifecycleEventConsumption;

use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleDeliveryPayload;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRouter;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportEnvelope;
use App\Application\PublicMediaMaterialization\Contract\AffectedPublicMediaListingReaderV1;
use App\Application\PublicMediaMaterialization\Contract\MaterializePublicMediaDecisionV2;
use App\Application\PublicMediaMaterialization\PublicMediaMaterializationStatus;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Throwable;

final readonly class MediaItemLifecycleDeliveryConsumer implements PublicProjectionDeliveryConsumer
{
    public function __construct(
        private MediaItemLifecycleEventRouter $router,
        private MediaItemLifecycleDeliveryConsumptionPolicy $policy,
        private ?AffectedPublicMediaListingReaderV1 $affectedListings = null,
        private ?MaterializePublicMediaDecisionV2 $publicMedia = null,
    ) {}

    public function consume(PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumptionResult
    {
        if (! $message->payload instanceof MediaItemLifecycleDeliveryPayload) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        try {
            $payload = MediaItemLifecycleDeliveryPayload::restore($message->payload->fields());
            $event = $payload->event;
            if ($message->eventType->value !== $event->metadata->eventType->value
                || $message->payloadVersion->value !== $event->metadata->payloadVersion->value
                || $message->sourceModule->value !== 'Media'
                || $message->aggregateType->value !== 'MediaItemLifecycle'
                || $message->aggregateId->value !== $event->payload->mediaId->value
                || $message->order->aggregateVersion !== $event->payload->occurredVersion
                || $message->order->eventIndex->value !== 1) {
                return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
            }
            $envelope = MediaItemLifecycleTransportEnvelope::wrap($payload);
        } catch (Throwable) {
            return PublicProjectionDeliveryConsumptionResult::DivergentPayload;
        }

        $consumption = $this->policy->consumptionFor($this->router->route($envelope));
        if ($consumption !== PublicProjectionDeliveryConsumptionResult::Consumed || $this->affectedListings === null || $this->publicMedia === null) {
            return $consumption;
        }
        $listingId = $this->affectedListings->listingIdForMedia($event->payload->mediaId->value);
        if ($listingId === null) {
            return PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness;
        }

        return match ($this->publicMedia->materialize(ListingId::fromString($listingId))->status) {
            PublicMediaMaterializationStatus::Applied,
            PublicMediaMaterializationStatus::AlreadyApplied,
            PublicMediaMaterializationStatus::RejectedObsolete => PublicProjectionDeliveryConsumptionResult::Consumed,
            PublicMediaMaterializationStatus::SourceMissing,
            PublicMediaMaterializationStatus::SourceNotReady => PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness,
            PublicMediaMaterializationStatus::DependencyUnavailable => PublicProjectionDeliveryConsumptionResult::RetryableFailure,
            PublicMediaMaterializationStatus::SourceCorrupted,
            PublicMediaMaterializationStatus::Divergent => PublicProjectionDeliveryConsumptionResult::PermanentFailure,
        };
    }
}
