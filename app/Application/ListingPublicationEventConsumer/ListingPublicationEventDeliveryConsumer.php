<?php

namespace App\Application\ListingPublicationEventConsumer;

use App\Application\ListingPublicationEventTransport\Contract\ListingPublicationEventRouter;
use App\Application\ListingPublicationEventTransport\ListingPublicationDeliveryPayload;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventRoutingStatus;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventTransportException;
use App\Application\PublicGeographyMaterialization\Contract\MaterializePublicGeographyDecisionV2;
use App\Application\PublicGeographyMaterialization\PublicGeographyMaterializationStatus;
use App\Application\PublicMediaMaterialization\Contract\MaterializePublicMediaDecisionV2;
use App\Application\PublicMediaMaterialization\PublicMediaMaterializationStatus;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use Appart\Modules\ContentSeo\Application\Materialization\ContentSeoMaterializationStatus;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\MaterializeContentSeoSnapshotV1;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId as ContentSeoListingId;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventType;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\MaterializePublicSearchDecisionV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchMaterializationStatus;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use DateTimeImmutable;
use DateTimeZone;

final readonly class ListingPublicationEventDeliveryConsumer implements PublicProjectionDeliveryConsumer
{
    public function __construct(
        private ListingPublicationEventRouter $router,
        private MaterializePublicSearchDecisionV1 $searchDecisions,
        private MaterializeContentSeoSnapshotV1 $contentSeoSnapshots,
        private ?MaterializePublicGeographyDecisionV2 $publicGeography = null,
        private ?MaterializePublicMediaDecisionV2 $publicMedia = null,
    ) {}

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

        $routing = $this->router->route($event)->status;
        if ($routing !== ListingPublicationEventRoutingStatus::Routed) {
            return match ($routing) {
                ListingPublicationEventRoutingStatus::Deferred => PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness,
                ListingPublicationEventRoutingStatus::RetryableFailure => PublicProjectionDeliveryConsumptionResult::RetryableFailure,
                ListingPublicationEventRoutingStatus::Rejected => PublicProjectionDeliveryConsumptionResult::PermanentFailure,
            };
        }
        if ($event->type !== ListingPublicationEventType::ListingPublished) {
            return PublicProjectionDeliveryConsumptionResult::Consumed;
        }

        if ($this->publicGeography !== null) {
            $geography = match ($this->publicGeography->materialize(\Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId::fromString($event->payload->listingId->value))->status) {
                PublicGeographyMaterializationStatus::Applied,
                PublicGeographyMaterializationStatus::AlreadyApplied,
                PublicGeographyMaterializationStatus::RejectedObsolete => PublicProjectionDeliveryConsumptionResult::Consumed,
                PublicGeographyMaterializationStatus::SourceMissing,
                PublicGeographyMaterializationStatus::SourceNotReady => PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness,
                PublicGeographyMaterializationStatus::DependencyUnavailable => PublicProjectionDeliveryConsumptionResult::RetryableFailure,
                PublicGeographyMaterializationStatus::SourceCorrupted,
                PublicGeographyMaterializationStatus::Divergent => PublicProjectionDeliveryConsumptionResult::PermanentFailure,
            };
            if ($geography !== PublicProjectionDeliveryConsumptionResult::Consumed) {
                return $geography;
            }
        }

        if ($this->publicMedia !== null) {
            $media = match ($this->publicMedia->materialize(\Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId::fromString($event->payload->listingId->value))->status) {
                PublicMediaMaterializationStatus::Applied,
                PublicMediaMaterializationStatus::AlreadyApplied,
                PublicMediaMaterializationStatus::RejectedObsolete => PublicProjectionDeliveryConsumptionResult::Consumed,
                PublicMediaMaterializationStatus::SourceMissing,
                PublicMediaMaterializationStatus::SourceNotReady => PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness,
                PublicMediaMaterializationStatus::DependencyUnavailable => PublicProjectionDeliveryConsumptionResult::RetryableFailure,
                PublicMediaMaterializationStatus::SourceCorrupted,
                PublicMediaMaterializationStatus::Divergent => PublicProjectionDeliveryConsumptionResult::PermanentFailure,
            };
            if ($media !== PublicProjectionDeliveryConsumptionResult::Consumed) {
                return $media;
            }
        }

        $search = match ($this->searchDecisions->materialize(ListingId::fromString($event->payload->listingId->value))->status) {
            PublicSearchMaterializationStatus::Applied,
            PublicSearchMaterializationStatus::AlreadyApplied,
            PublicSearchMaterializationStatus::RejectedObsolete => PublicProjectionDeliveryConsumptionResult::Consumed,
            PublicSearchMaterializationStatus::SourceMissing => PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness,
            PublicSearchMaterializationStatus::DependencyUnavailable => PublicProjectionDeliveryConsumptionResult::RetryableFailure,
            PublicSearchMaterializationStatus::SourceCorrupted,
            PublicSearchMaterializationStatus::Divergent => PublicProjectionDeliveryConsumptionResult::PermanentFailure,
        };
        if ($search !== PublicProjectionDeliveryConsumptionResult::Consumed) {
            return $search;
        }

        return match ($this->contentSeoSnapshots->materialize(ContentSeoListingId::fromString($event->payload->listingId->value))->status) {
            ContentSeoMaterializationStatus::Applied,
            ContentSeoMaterializationStatus::AlreadyApplied,
            ContentSeoMaterializationStatus::RejectedObsolete => PublicProjectionDeliveryConsumptionResult::Consumed,
            ContentSeoMaterializationStatus::SourceMissing,
            ContentSeoMaterializationStatus::SourceNotReady => PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness,
            ContentSeoMaterializationStatus::DependencyUnavailable => PublicProjectionDeliveryConsumptionResult::RetryableFailure,
            ContentSeoMaterializationStatus::SourceCorrupted,
            ContentSeoMaterializationStatus::Divergent => PublicProjectionDeliveryConsumptionResult::PermanentFailure,
        };
    }

    private function canonicalUtc(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
