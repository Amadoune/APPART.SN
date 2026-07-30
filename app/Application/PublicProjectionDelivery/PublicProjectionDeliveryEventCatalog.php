<?php

namespace App\Application\PublicProjectionDelivery;

use App\Application\AccountStatusEventTransport\AccountStatusDeliveryPayload;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleDeliveryPayload;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleDeliveryPayload;
use App\Application\ListingPublicationEventTransport\ListingPublicationDeliveryPayload;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleDeliveryPayload;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleDeliveryPayload;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusDeliveryPayload;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleDeliveryPayload;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryContentSeoPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryMediaPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryPropertyPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliverySearchPayload;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleDeliveryPayload;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventType;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventType;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventType;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventType;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventType;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventType;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventType;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventType;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventType;

final readonly class PublicProjectionDeliveryEventCatalog
{
    /** @var array<string, PublicProjectionDeliveryCatalogEntry> */
    private array $entries;

    /** @param list<PublicProjectionDeliveryCatalogEntry>|null $entries */
    public function __construct(?array $entries = null)
    {
        $entries ??= self::officialEntries();
        $indexed = [];
        foreach ($entries as $entry) {
            if (isset($indexed[$entry->eventType->value])) {
                throw new \InvalidArgumentException('Duplicate Public Projection Delivery event type.');
            }
            $indexed[$entry->eventType->value] = $entry;
        }
        $this->entries = $indexed;
    }

    public function compatibility(PublicProjectionDeliveryEventType $eventType, PublicProjectionDeliveryPayloadVersion $version): PublicProjectionDeliveryCompatibility
    {
        $entry = $this->entries[$eventType->value] ?? null;
        if ($entry === null) {
            return PublicProjectionDeliveryCompatibility::UnsupportedType;
        }
        if ($entry->payloadVersion->value !== $version->value) {
            return PublicProjectionDeliveryCompatibility::UnsupportedVersion;
        }

        return $entry->deprecated ? PublicProjectionDeliveryCompatibility::DeprecatedButSupported : PublicProjectionDeliveryCompatibility::Supported;
    }

    public function accepts(
        PublicProjectionDeliveryEventType $eventType,
        PublicProjectionDeliveryPayloadVersion $version,
        PublicProjectionDeliverySourceModule $sourceModule,
        PublicProjectionDeliveryAggregateType $aggregateType,
        PublicProjectionDeliveryPayload $payload,
    ): bool {
        $entry = $this->entries[$eventType->value] ?? null;

        return $entry !== null
            && $entry->payloadVersion->value === $version->value
            && $entry->sourceModule == $sourceModule
            && $entry->aggregateType == $aggregateType
            && $payload instanceof $entry->payloadClass
            && $this->payloadIdentity($payload) !== null;
    }

    /** @return list<PublicProjectionDeliveryCatalogEntry> */
    private static function officialEntries(): array
    {
        $entries = [
            self::entry('listing.reconstruction.requested', 'ListingLifecycle', 'Listing', PublicProjectionDeliveryListingPayload::class),
            self::entry('property.reconstruction.requested', 'RealEstateCatalog', 'Property', PublicProjectionDeliveryPropertyPayload::class),
            self::entry('media.reconstruction.requested', 'Media', 'MediaCollection', PublicProjectionDeliveryMediaPayload::class),
            self::entry('search.reconstruction.requested', 'SearchDiscovery', 'SearchIndex', PublicProjectionDeliverySearchPayload::class),
            self::entry('content_seo.reconstruction.requested', 'ContentSeo', 'SeoProjection', PublicProjectionDeliveryContentSeoPayload::class),
        ];
        foreach (ListingPublicationEventType::cases() as $eventType) {
            $entries[] = self::entry($eventType->value, 'ListingLifecycle', 'Listing', ListingPublicationDeliveryPayload::class);
        }
        foreach (PropertyLifecycleEventType::cases() as $eventType) {
            $entries[] = self::entry($eventType->value, 'RealEstateCatalog', 'Property', PropertyLifecycleDeliveryPayload::class);
        }
        foreach (ReservationLifecycleEventType::cases() as $eventType) {
            $entries[] = self::entry($eventType->value, 'ReservationLifecycle', 'ReservationLifecycle', ReservationLifecycleDeliveryPayload::class);
        }
        foreach (LeadLifecycleEventType::cases() as $eventType) {
            $entries[] = self::entry($eventType->value, 'ContactsLeads', 'LeadLifecycle', LeadLifecycleDeliveryPayload::class);
        }
        foreach (ProfessionalStatusEventType::cases() as $eventType) {
            $entries[] = self::entry($eventType->value, 'Professionals', 'ProfessionalStatus', ProfessionalStatusDeliveryPayload::class);
        }
        foreach (MediaItemLifecycleEventType::cases() as $eventType) {
            $entries[] = self::entry($eventType->value, 'Media', 'MediaItemLifecycle', MediaItemLifecycleDeliveryPayload::class);
        }
        foreach (AdministrativeActionLifecycleEventType::cases() as $eventType) {
            $entries[] = self::entry($eventType->value, 'AdministrationAudit', 'AdministrativeActionLifecycle', AdministrativeActionLifecycleDeliveryPayload::class);
        }
        foreach (PlaceLifecycleEventType::cases() as $eventType) {
            $entries[] = self::entry($eventType->value, 'Geography', 'PlaceLifecycle', PlaceLifecycleDeliveryPayload::class);
        }
        foreach (AccountStatusEventType::cases() as $eventType) {
            $entries[] = self::entry($eventType->value, 'IdentityAccess', 'AccountStatus', AccountStatusDeliveryPayload::class);
        }

        return $entries;
    }

    /** @param class-string<PublicProjectionDeliveryPayload> $payloadClass */
    private static function entry(string $eventType, string $module, string $aggregate, string $payloadClass): PublicProjectionDeliveryCatalogEntry
    {
        return new PublicProjectionDeliveryCatalogEntry(
            PublicProjectionDeliveryEventType::fromString($eventType),
            PublicProjectionDeliverySourceModule::fromString($module),
            PublicProjectionDeliveryAggregateType::fromString($aggregate),
            PublicProjectionDeliveryPayloadVersion::fromInt(1),
            $payloadClass,
        );
    }

    private function payloadIdentity(PublicProjectionDeliveryPayload $payload): ?string
    {
        return match (true) {
            $payload instanceof PublicProjectionDeliveryListingPayload => $payload->listingId,
            $payload instanceof PublicProjectionDeliveryPropertyPayload => $payload->propertyId,
            $payload instanceof PublicProjectionDeliveryMediaPayload => $payload->mediaCollectionId,
            $payload instanceof PublicProjectionDeliverySearchPayload => $payload->listingId,
            $payload instanceof PublicProjectionDeliveryContentSeoPayload => $payload->listingId,
            $payload instanceof ListingPublicationDeliveryPayload => $payload->event->payload->listingId->value,
            $payload instanceof PropertyLifecycleDeliveryPayload => $payload->event->payload->propertyId->value,
            $payload instanceof ReservationLifecycleDeliveryPayload => $payload->event->payload->reservationId->value,
            $payload instanceof LeadLifecycleDeliveryPayload => $payload->event->payload->leadId->value,
            $payload instanceof ProfessionalStatusDeliveryPayload => $payload->event->payload->professionalId->value,
            $payload instanceof MediaItemLifecycleDeliveryPayload => $payload->event->payload->mediaId->value,
            $payload instanceof AdministrativeActionLifecycleDeliveryPayload => $payload->event->payload->actionId->value,
            $payload instanceof PlaceLifecycleDeliveryPayload => $payload->event->payload->placeId->value,
            $payload instanceof AccountStatusDeliveryPayload => $payload->event->payload->accountId->value,
            default => null,
        };
    }
}
