<?php

namespace App\Application\RuntimeHealth;

use App\Application\AdministrativeActionLifecycleEventRouting\AdministrativeActionLifecycleInboxStore;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRouter;
use App\Application\LeadLifecycleEventRouting\LeadLifecycleInboxStore;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRouter;
use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationEventOrchestrator;
use App\Application\ListingPublicationEventRouting\Contract\ListingPublicationEventDestination;
use App\Application\ListingPublicationEventTransport\Contract\ListingPublicationEventRouter;
use App\Application\MediaItemLifecycleEventRouting\MediaItemLifecycleInboxStore;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRouter;
use App\Application\MultiTargetDelivery\Contract\MultiTargetPropagationStrategy;
use App\Application\PlaceLifecycleEventConsumption\PlaceLifecycleDeliveryConsumer;
use App\Application\PlaceLifecycleEventRouting\PlaceLifecycleInboxStore;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRouter;
use App\Application\ProfessionalStatusEventRouting\ProfessionalStatusInboxStore;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRouter;
use App\Application\ProjectionRuntimeSource\Contract\InspectablePublicListingProjectionSource;
use App\Application\PropertyLifecycleEventIntegration\Contract\PropertyLifecycleEventOrchestrator;
use App\Application\PropertyLifecycleEventRouting\Contract\PropertyLifecycleEventDestination;
use App\Application\PropertyLifecycleEventTransport\Contract\PropertyLifecycleEventRouter;
use App\Application\PublicGeographySource\Contract\PublicGeographyDecisionReader;
use App\Application\PublicMediaSource\Contract\PublicMediaDecisionReader;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionRebuildEnumerator;
use App\Application\PublicProjectionSourceLookup\Contract\MediaCollectionPropertyResolver;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionUpdaterIntegration\Contract\PublicProjectionSourceLookup;
use App\Application\PublicProjectionUpdaterIntegration\Contract\PublicProjectionUpdateExecutor;
use App\Application\ReservationLifecycleEventRouting\ReservationLifecycleInboxStore;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleEventRouterPort;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleWorkflow;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\Contract\AdministrativeActionLifecycleOrchestrator;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\Contract\AdministrativeActionLifecycleWorkflowStore;
use Appart\Modules\ContactsLeads\Application\Contract\AdvertiserCatalog;
use Appart\Modules\ContactsLeads\Application\Contract\ListingCatalog;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract\LeadEligibilityDecisionMaterializer;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleWorkflow;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\Contract\LeadLifecycleOrchestrator;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\Contract\LeadLifecycleWorkflowStore;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleWorkflow;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\Contract\PlaceLifecycleWorkflowStore;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusWorkflow;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\Contract\AccountStatusOrchestrator;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\Contract\AccountStatusWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleWorkflow;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\Contract\MediaItemLifecycleOrchestrator;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\Contract\MediaItemLifecycleWorkflowStore;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\Contract\ProfessionalMandateResolverV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusWorkflow;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\Contract\ProfessionalStatusOrchestrator;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\Contract\ProfessionalStatusWorkflowStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\Contract\ProfessionalPublicStatusReaderV1;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract\PropertyLifecycleOrchestrator;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract\PropertyLifecycleWorkflowStore;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleWorkflow;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleWorkflow;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\Contract\ReservationLifecycleWorkflowStore;

final class PublicProjectionRuntimeRequirements
{
    /** @return list<RuntimeHealthRequirement> */
    public static function certified(): array
    {
        return [
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProjectionSourceLookup, PublicProjectionSourceLookup::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProjectionRuntimeSource, InspectablePublicListingProjectionSource::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::PublicGeographySource, PublicGeographyDecisionReader::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::PublicMediaSource, PublicMediaDecisionReader::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::MediaCollectionPropertyResolver, MediaCollectionPropertyResolver::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::MultiTargetStrategy, MultiTargetPropagationStrategy::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProjectionUpdater, PublicProjectionUpdateExecutor::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProjectionStore, PublicListingProjectionWriter::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::RebuildEnumerator, PublicProjectionRebuildEnumerator::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::DeliveryConsumer, PublicProjectionDeliveryConsumer::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::HistoricalRedirectResolver, HistoricalRedirectResolver::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::HistoricalCanonicalQualifier, HistoricalCanonicalQualifier::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ListingPublicationWorkflow, ListingPublicationWorkflow::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ListingPublicationWorkflowStore, ListingPublicationWorkflowStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ListingPublicationEventDestination, ListingPublicationEventDestination::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ListingPublicationEventRouter, ListingPublicationEventRouter::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ListingPublicationEventOrchestrator, ListingPublicationEventOrchestrator::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::PropertyLifecycleWorkflow, PropertyLifecycleWorkflow::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::PropertyLifecycleWorkflowStore, PropertyLifecycleWorkflowStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::PropertyLifecycleOrchestrator, PropertyLifecycleOrchestrator::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::PropertyLifecycleEventDestination, PropertyLifecycleEventDestination::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::PropertyLifecycleEventRouter, PropertyLifecycleEventRouter::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::PropertyLifecycleEventOrchestrator, PropertyLifecycleEventOrchestrator::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ReservationLifecycleWorkflow, ReservationLifecycleWorkflow::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ReservationLifecycleWorkflowStore, ReservationLifecycleWorkflowStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ReservationLifecycleInboxStore, ReservationLifecycleInboxStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ReservationLifecycleEventRouter, ReservationLifecycleEventRouterPort::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::LeadLifecycleWorkflow, LeadLifecycleWorkflow::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::LeadLifecycleWorkflowStore, LeadLifecycleWorkflowStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::LeadLifecycleOrchestrator, LeadLifecycleOrchestrator::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::LeadLifecycleInboxStore, LeadLifecycleInboxStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::LeadLifecycleEventRouter, LeadLifecycleEventRouter::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::LeadEligibilityDecisionMaterializer, LeadEligibilityDecisionMaterializer::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ListingCatalog, ListingCatalog::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::AdvertiserCatalog, AdvertiserCatalog::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProfessionalStatusWorkflow, ProfessionalStatusWorkflow::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProfessionalStatusWorkflowStore, ProfessionalStatusWorkflowStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProfessionalStatusOrchestrator, ProfessionalStatusOrchestrator::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProfessionalStatusInboxStore, ProfessionalStatusInboxStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProfessionalStatusEventRouter, ProfessionalStatusEventRouter::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProfessionalMandateResolver, ProfessionalMandateResolverV1::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::ProfessionalPublicStatusReader, ProfessionalPublicStatusReaderV1::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::MediaItemLifecycleWorkflow, MediaItemLifecycleWorkflow::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::MediaItemLifecycleWorkflowStore, MediaItemLifecycleWorkflowStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::MediaItemLifecycleOrchestrator, MediaItemLifecycleOrchestrator::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::MediaItemLifecycleInboxStore, MediaItemLifecycleInboxStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::MediaItemLifecycleEventRouter, MediaItemLifecycleEventRouter::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::AdministrativeActionLifecycleWorkflow, AdministrativeActionLifecycleWorkflow::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::AdministrativeActionLifecycleWorkflowStore, AdministrativeActionLifecycleWorkflowStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::AdministrativeActionLifecycleOrchestrator, AdministrativeActionLifecycleOrchestrator::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::AdministrativeActionLifecycleInboxStore, AdministrativeActionLifecycleInboxStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::AdministrativeActionLifecycleEventRouter, AdministrativeActionLifecycleEventRouter::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::PlaceLifecycleWorkflow, PlaceLifecycleWorkflow::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::PlaceLifecycleWorkflowStore, PlaceLifecycleWorkflowStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::PlaceLifecycleInboxStore, PlaceLifecycleInboxStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::PlaceLifecycleEventRouter, PlaceLifecycleEventRouter::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::PlaceLifecycleDeliveryConsumer, PlaceLifecycleDeliveryConsumer::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::AccountStatusWorkflow, AccountStatusWorkflow::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::AccountStatusWorkflowStore, AccountStatusWorkflowStore::class),
            new RuntimeHealthRequirement(RuntimeHealthComponent::AccountStatusOrchestrator, AccountStatusOrchestrator::class),
        ];
    }
}
