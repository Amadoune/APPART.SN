<?php

namespace App\Providers;

use App\Application\AccountStatusEventConsumption\AccountStatusDeliveryConsumer;
use App\Application\AccountStatusEventIntegration\AccountStatusAtomicEventOrchestrator;
use App\Application\AccountStatusEventIntegration\Contract\AccountStatusAtomicTransaction;
use App\Application\AccountStatusEventRouting\AccountStatusEventRouter;
use App\Application\AccountStatusEventRouting\DeterministicAccountStatusEventRouter;
use App\Application\AccountStatusEventTransport\AccountStatusTransportSerializer;
use App\Application\ActiveGenerationReader\Contract\ActiveGenerationReader;
use App\Application\AdministrativeActionLifecycleEventConsumption\AdministrativeActionLifecycleDeliveryConsumer;
use App\Application\AdministrativeActionLifecycleEventConsumption\AdministrativeActionLifecycleDeliveryConsumptionPolicy;
use App\Application\AdministrativeActionLifecycleEventIntegration\AdministrativeActionLifecycleAtomicEventOrchestrator;
use App\Application\AdministrativeActionLifecycleEventIntegration\Contract\AdministrativeActionLifecycleAtomicTransaction;
use App\Application\AdministrativeActionLifecycleEventRouting\AdministrativeActionLifecycleInboxStore;
use App\Application\AdministrativeActionLifecycleEventRouting\DurableAdministrativeActionLifecycleEventRouter;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRouter;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleTransportSerializer;
use App\Application\Contract\PublicListingQuery;
use App\Application\DecisionTimeSource\Contract\DecisionTimeReader;
use App\Application\LeadLifecycleEventConsumption\LeadLifecycleDeliveryConsumer;
use App\Application\LeadLifecycleEventConsumption\LeadLifecycleDeliveryConsumptionPolicy;
use App\Application\LeadLifecycleEventIntegration\Contract\LeadLifecycleAtomicTransaction;
use App\Application\LeadLifecycleEventIntegration\LeadLifecycleAtomicEventOrchestrator;
use App\Application\LeadLifecycleEventRouting\DurableLeadLifecycleEventRouter;
use App\Application\LeadLifecycleEventRouting\LeadLifecycleInboxStore;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRouter;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleTransportSerializer;
use App\Application\ListingPublicationEventConsumer\ListingPublicationEventDeliveryConsumer;
use App\Application\ListingPublicationEventIntegration\AtomicListingPublicationEventOrchestrator;
use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationAtomicTransaction;
use App\Application\ListingPublicationEventIntegration\Contract\ListingPublicationEventOrchestrator;
use App\Application\ListingPublicationEventRouting\Contract\ListingPublicationEventDestination;
use App\Application\ListingPublicationEventRouting\DurableListingPublicationEventRouter;
use App\Application\ListingPublicationEventTransport\Contract\ListingPublicationEventRouter;
use App\Application\MediaItemLifecycleEventConsumption\MediaItemLifecycleDeliveryConsumer;
use App\Application\MediaItemLifecycleEventConsumption\MediaItemLifecycleDeliveryConsumptionPolicy;
use App\Application\MediaItemLifecycleEventIntegration\Contract\MediaItemLifecycleAtomicTransaction;
use App\Application\MediaItemLifecycleEventIntegration\MediaItemLifecycleAtomicEventOrchestrator;
use App\Application\MediaItemLifecycleEventRouting\DurableMediaItemLifecycleEventRouter;
use App\Application\MediaItemLifecycleEventRouting\MediaItemLifecycleInboxStore;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRouter;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportSerializer;
use App\Application\MultiTargetDelivery\Contract\MultiTargetPropagationStrategy;
use App\Application\MultiTargetDelivery\PagedMultiTargetPropagationStrategy;
use App\Application\PlaceLifecycleEventConsumption\PlaceLifecycleDeliveryConsumer;
use App\Application\PlaceLifecycleEventConsumption\PlaceLifecycleDeliveryConsumptionPolicy;
use App\Application\PlaceLifecycleEventIntegration\Contract\PlaceLifecycleAtomicTransaction;
use App\Application\PlaceLifecycleEventIntegration\PlaceLifecycleAtomicEventOrchestrator;
use App\Application\PlaceLifecycleEventRouting\DurablePlaceLifecycleEventRouter;
use App\Application\PlaceLifecycleEventRouting\PlaceLifecycleInboxStore;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRouter;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleTransportSerializer;
use App\Application\ProfessionalStatusEventConsumption\ProfessionalStatusDeliveryConsumer;
use App\Application\ProfessionalStatusEventConsumption\ProfessionalStatusDeliveryConsumptionPolicy;
use App\Application\ProfessionalStatusEventIntegration\Contract\ProfessionalStatusAtomicTransaction;
use App\Application\ProfessionalStatusEventIntegration\ProfessionalStatusAtomicEventOrchestrator;
use App\Application\ProfessionalStatusEventRouting\DurableProfessionalStatusEventRouter;
use App\Application\ProfessionalStatusEventRouting\ProfessionalStatusInboxStore;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRouter;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportSerializer;
use App\Application\ProjectionRebuildRuntimeSource\Contract\InspectablePublicProjectionCandidateFactory;
use App\Application\ProjectionRuntimeSource\Contract\InspectablePublicListingProjectionSource;
use App\Application\PropertyLifecycleEventConsumer\PropertyLifecycleEventDeliveryConsumer;
use App\Application\PropertyLifecycleEventIntegration\AtomicPropertyLifecycleEventOrchestrator;
use App\Application\PropertyLifecycleEventIntegration\Contract\PropertyLifecycleAtomicTransaction;
use App\Application\PropertyLifecycleEventIntegration\Contract\PropertyLifecycleEventOrchestrator;
use App\Application\PropertyLifecycleEventRouting\Contract\PropertyLifecycleEventDestination;
use App\Application\PropertyLifecycleEventRouting\DurablePropertyLifecycleEventRouter;
use App\Application\PropertyLifecycleEventTransport\Contract\PropertyLifecycleEventRouter;
use App\Application\PropertyListingResolution\Contract\PropertyListingsResolver;
use App\Application\PublicGeographySource\Contract\PublicGeographyDecisionReader;
use App\Application\PublicMediaSource\Contract\PublicMediaDecisionReader;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxClaimManager;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxReader;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxRetryPolicy;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxRoutedWriterV1;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionCandidateFactory;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionGenerationManager;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionGenerationValidator;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionRebuildEnumerator;
use App\Application\PublicProjectionRetry\PublicProjectionDeterministicRetryPolicy;
use App\Application\PublicProjectionRetry\PublicProjectionFixedBackoff;
use App\Application\PublicProjectionSourceLookup\Contract\MediaCollectionPropertyResolver;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionUpdater\Contract\PublicListingProjectionSource;
use App\Application\PublicProjectionUpdaterIntegration\Contract\PublicProjectionSourceLookup;
use App\Application\PublicProjectionUpdaterIntegration\Contract\PublicProjectionUpdateExecutor;
use App\Application\PublicProjectionUpdaterIntegration\PublicListingProjectionUpdaterExecutor;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionUpdaterConsumer;
use App\Application\PublicProjectionWorker\Contract\PublicProjectionDeliveryClock;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistration;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistry;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryMode;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorker;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorkerId;
use App\Application\ReservationLifecycleEventConsumer\ReservationLifecycleDeliveryConsumer;
use App\Application\ReservationLifecycleEventConsumer\ReservationLifecycleDeliveryConsumptionPolicy;
use App\Application\ReservationLifecycleEventIntegration\Contract\ReservationLifecycleAtomicTransaction;
use App\Application\ReservationLifecycleEventIntegration\ReservationLifecycleAtomicEventOrchestrator;
use App\Application\ReservationLifecycleEventRouting\DeterministicReservationLifecycleEventRouter;
use App\Application\ReservationLifecycleEventRouting\ReservationLifecycleInboxStore;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleEventRouterPort;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportSerializer;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\DeterministicRuntimeHealthInspector;
use App\Application\RuntimeHealth\PublicProjectionRuntimeRequirements;
use App\Application\RuntimeHealth\RuntimeHealthComponent;
use App\Application\RuntimeHealth\RuntimeHealthRegistration;
use App\Infrastructure\ActiveGenerationReader\PostgreSql\PostgreSqlActiveGenerationReader;
use App\Infrastructure\AdministrativeActionLifecycleEventRouting\PostgreSql\PostgreSqlAdministrativeActionLifecycleInboxRepository;
use App\Infrastructure\DecisionTimeSource\ContentSeoSnapshotDecisionTimeReader;
use App\Infrastructure\LeadLifecycleEventRouting\PostgreSql\PostgreSqlLeadLifecycleInboxRepository;
use App\Infrastructure\ListingPublicationEventRouting\PostgreSql\PostgreSqlListingPublicationEventInbox;
use App\Infrastructure\MediaItemLifecycleEventRouting\PostgreSql\PostgreSqlMediaItemLifecycleInboxRepository;
use App\Infrastructure\PlaceLifecycleEventRouting\PostgreSql\PostgreSqlPlaceLifecycleInboxRepository;
use App\Infrastructure\ProfessionalStatusEventRouting\PostgreSql\PostgreSqlProfessionalStatusInboxRepository;
use App\Infrastructure\ProjectionRebuildRuntimeSource\CertifiedPublicProjectionCandidateFactory;
use App\Infrastructure\ProjectionRebuildRuntimeSource\PostgreSql\PostgreSqlPublicProjectionRebuildEnumerator;
use App\Infrastructure\ProjectionRuntimeSource\CertifiedPublicListingProjectionSource;
use App\Infrastructure\PropertyLifecycleEventRouting\PostgreSql\PostgreSqlPropertyLifecycleEventInbox;
use App\Infrastructure\PropertyListingResolution\PostgreSql\PostgreSqlPropertyListingsResolver;
use App\Infrastructure\PublicGeographySource\PostgreSql\PostgreSqlPublicGeographyReader;
use App\Infrastructure\PublicMediaSource\PostgreSql\PostgreSqlPublicMediaReader;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxParticipantTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxClaimManager;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxReader;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use App\Infrastructure\PublicProjectionSourceLookup\CertifiedPublicProjectionSourceLookup;
use App\Infrastructure\PublicProjectionSourceLookup\RegistryMediaCollectionPropertyResolver;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionStore;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicProjectionGenerationManager;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicProjectionGenerationValidator;
use App\Infrastructure\PublicProjectionWorker\SystemPublicProjectionDeliveryClock;
use App\Infrastructure\ReservationLifecycleEventRouting\PostgreSql\PostgreSqlReservationLifecycleInboxRepository;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionEnrollmentCanonicalizer;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleWorkflow;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventCatalog;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventType;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\Contract\AdministrativeActionLifecycleOrchestrator;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleOrchestration\DeterministicAdministrativeActionLifecycleOrchestrator;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecyclePersistence\Contract\AdministrativeActionLifecycleWorkflowStore;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\AdministrativeActionReplayPolicy;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualReplayInspector;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext\Contract\AdministrativeActionContextualTransitionStore;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionLifecycleWorkflowMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\AdministrativeActionTransitionContextMapper;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionContextualReplayInspector;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionContextualTransitionRepository;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionLifecycleAtomicPersistenceTransaction;
use Appart\Modules\AdministrationAudit\Infrastructure\Persistence\PostgreSql\PostgreSqlAdministrativeActionLifecycleRepository;
use Appart\Modules\ContactsLeads\Application\Contract\AdvertiserCatalog;
use Appart\Modules\ContactsLeads\Application\Contract\ListingCatalog;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract\LeadEligibilityDecisionMaterializer;
use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\Contract\LeadEligibilitySourceDataReader;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleWorkflow;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventType;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\Contract\LeadLifecycleOrchestrator;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleOrchestration\DeterministicLeadLifecycleOrchestrator;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\Contract\LeadLifecycleWorkflowStore;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualReplayInspector;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\Contract\LeadLifecycleContextualTransitionStore;
use Appart\Modules\ContactsLeads\Infrastructure\EligibilitySource\MaterializedAdvertiserCatalog;
use Appart\Modules\ContactsLeads\Infrastructure\EligibilitySource\MaterializedListingCatalog;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadEligibilitySourceDataMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleContextMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadLifecycleWorkflowMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadEligibilityDecisionStore;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualReplayInspector;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleContextualTransitionRepository;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadLifecycleWorkflowRepository;
use Appart\Modules\ContentSeo\Application\Contract\ContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\HistoricalCanonicalQualificationMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\HistoricalRedirectDecisionMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlContentSeoSourceSnapshotReader;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlHistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlHistoricalRedirectResolver;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleWorkflow;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventCatalog;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventType;
use Appart\Modules\Geography\Application\PlaceLifecycleOrchestration\PlaceLifecycleOrchestrator;
use Appart\Modules\Geography\Application\PlaceLifecyclePersistence\Contract\PlaceLifecycleWorkflowStore;
use Appart\Modules\Geography\Application\PlaceMergeContext\Contract\PlaceMergeContextInspector;
use Appart\Modules\Geography\Application\PlaceMergeContext\Contract\PlaceMergeReplayClassifier;
use Appart\Modules\Geography\Application\PlaceMergeContext\DeterministicPlaceMergeReplayClassifier;
use Appart\Modules\Geography\Infrastructure\Persistence\PlaceLifecycleWorkflowMapper;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceLifecycleWorkflowStore;
use Appart\Modules\Geography\Infrastructure\Persistence\PostgreSql\PostgreSqlPlaceMergeContextInspector;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventCatalog;
use Appart\Modules\IdentityAccess\Application\AccountStatusEvent\AccountStatusEventType;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusWorkflow;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\Contract\AccountStatusOrchestrationTransaction;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\Contract\AccountStatusOrchestrator;
use Appart\Modules\IdentityAccess\Application\AccountStatusOrchestration\DeterministicAccountStatusOrchestrator;
use Appart\Modules\IdentityAccess\Application\AccountStatusPersistence\Contract\AccountStatusWorkflowStore;
use Appart\Modules\IdentityAccess\Application\Contract\AccountRegistry;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\AccountStatusWorkflowMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount\AccountPersistenceMapper;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountRepository;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountStatusOrchestrationTransaction;
use Appart\Modules\IdentityAccess\Infrastructure\Persistence\PostgreSql\PostgreSqlAccountStatusWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\Contract\ListingContactabilityReaderV1;
use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\OwnerListingContactabilityReaderV1;
use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\Contract\ListingModerationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\Contract\ListingModerationReaderV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\OwnerListingModerationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\OwnerListingModerationReaderV1;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\Contract\ListingModerationIntentStore;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\Contract\ListingModerationIntentTransaction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Contract\ListingPublicationWorkflowStore;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\DeterministicListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventSerializer;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventType;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingPublicationWorkflowMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingModerationIntentStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingModerationIntentTransaction;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationWorkflowRepository;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingRepository;
use Appart\Modules\Media\Application\Contract\MediaCollectionOwnershipLookup;
use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleWorkflow;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualTransitionStore;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleReplayPolicy;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventType;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\Contract\MediaItemLifecycleOrchestrator;
use Appart\Modules\Media\Application\MediaItemLifecycleOrchestration\DeterministicMediaItemLifecycleOrchestrator;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\Contract\MediaItemLifecycleWorkflowStore;
use Appart\Modules\Media\Infrastructure\Persistence\MediaCollectionMapper;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleContextMapper;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleWorkflowMapper;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaCollectionOwnershipLookup;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaCollectionRepository;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleContextualTransitionRepository;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaItemLifecycleWorkflowRepository;
use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\Contract\ProfessionalMandateOwnerSource;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\Contract\ProfessionalMandateResolverV1;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\OwnerProfessionalMandateResolverV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventType;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusWorkflow;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\Contract\ProfessionalStatusOrchestrator;
use Appart\Modules\Professionals\Application\ProfessionalStatusOrchestration\DeterministicProfessionalStatusOrchestrator;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\Contract\ProfessionalStatusWorkflowStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\Contract\ProfessionalPublicStatusReaderV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualReplayInspector;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualTransitionStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusReplayPolicy;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalMandateOwnerSource;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusContextualReplayInspector;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusContextualTransitionRepository;
use Appart\Modules\Professionals\Infrastructure\Persistence\PostgreSql\PostgreSqlProfessionalStatusWorkflowRepository;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalMandateOwnerSourceMapper;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusContextMapper;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalStatusWorkflowMapper;
use Appart\Modules\Professionals\Infrastructure\Runtime\OwnerProfessionalPublicStatusReaderV1;
use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract\PropertyLifecycleOrchestrator;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Contract\PropertyLifecycleWorkflowStore;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\DeterministicPropertyLifecycleOrchestrator;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventCatalog;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventSerializer;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventType;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleWorkflow;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyLifecycleWorkflowRepository;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyRepository;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyLifecycleWorkflowMapper;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PropertyMapper;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleWorkflow;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventType;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\Contract\ReservationLifecycleEventOrchestrator;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration\DeterministicReservationLifecycleEventOrchestrator;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\Contract\ReservationLifecycleWorkflowStore;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlReservationLifecycleWorkflowRepository;
use Appart\Modules\ReservationLifecycle\Infrastructure\Persistence\ReservationLifecycleWorkflowMapper;
use Appart\Modules\SearchDiscovery\Application\Contract\SearchDecisionReader;
use Appart\Modules\SearchDiscovery\Infrastructure\Persistence\PostgreSql\PostgreSqlSearchDecisionReader;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;
use PDO;

final class PublicProjectionRuntimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PDO::class, static fn (Application $app): PDO => $app->make(DatabaseManager::class)->connection('pgsql')->getPdo());

        $this->app->singleton(PostgreSqlAggregateOutboxTransaction::class);
        $this->app->alias(PostgreSqlAggregateOutboxTransaction::class, AdministrativeActionLifecycleAtomicTransaction::class);
        $this->app->alias(PostgreSqlAggregateOutboxTransaction::class, ListingPublicationAtomicTransaction::class);
        $this->app->alias(PostgreSqlAggregateOutboxTransaction::class, LeadLifecycleAtomicTransaction::class);
        $this->app->alias(PostgreSqlAggregateOutboxTransaction::class, ProfessionalStatusAtomicTransaction::class);
        $this->app->alias(PostgreSqlAggregateOutboxTransaction::class, MediaItemLifecycleAtomicTransaction::class);
        $this->app->alias(PostgreSqlAggregateOutboxTransaction::class, PlaceLifecycleAtomicTransaction::class);
        $this->app->alias(PostgreSqlAggregateOutboxTransaction::class, PropertyLifecycleAtomicTransaction::class);
        $this->app->alias(PostgreSqlAggregateOutboxTransaction::class, ReservationLifecycleAtomicTransaction::class);
        $this->app->singleton(PostgreSqlAggregateOutboxParticipantTransaction::class);
        $this->app->bind(ListingRegistry::class, static fn (Application $app): ListingRegistry => new PostgreSqlListingRepository(
            $app->make(PDO::class),
            $app->make(ListingMapper::class),
            $app->make(PostgreSqlAggregateOutboxParticipantTransaction::class),
        ));
        $this->app->bind(PropertyRegistry::class, static fn (Application $app): PropertyRegistry => new PostgreSqlPropertyRepository(
            $app->make(PDO::class),
            $app->make(PropertyMapper::class),
            $app->make(PostgreSqlAggregateOutboxParticipantTransaction::class),
        ));
        $this->app->bind(MediaCollectionRegistry::class, static fn (Application $app): MediaCollectionRegistry => new PostgreSqlMediaCollectionRepository(
            $app->make(PDO::class),
            $app->make(MediaCollectionMapper::class),
            $app->make(PostgreSqlAggregateOutboxParticipantTransaction::class),
        ));
        $this->app->bind(MediaCollectionOwnershipLookup::class, PostgreSqlMediaCollectionOwnershipLookup::class);
        $this->app->bind(SearchDecisionReader::class, PostgreSqlSearchDecisionReader::class);
        $this->app->bind(ContentSeoSourceSnapshotReader::class, PostgreSqlContentSeoSourceSnapshotReader::class);
        $this->app->singleton(HistoricalRedirectDecisionMapper::class);
        $this->app->singleton(PostgreSqlHistoricalRedirectResolver::class);
        $this->app->alias(PostgreSqlHistoricalRedirectResolver::class, HistoricalRedirectResolver::class);
        $this->app->singleton(HistoricalCanonicalQualificationMapper::class);
        $this->app->singleton(PostgreSqlHistoricalCanonicalQualifier::class);
        $this->app->alias(PostgreSqlHistoricalCanonicalQualifier::class, HistoricalCanonicalQualifier::class);
        $this->app->singleton(ListingPublicationWorkflow::class);
        $this->app->singleton(ListingPublicationWorkflowMapper::class);
        $this->app->singleton(PostgreSqlListingPublicationWorkflowRepository::class);
        $this->app->alias(PostgreSqlListingPublicationWorkflowRepository::class, ListingPublicationWorkflowStore::class);
        $this->app->singleton(PostgreSqlListingModerationIntentStore::class);
        $this->app->alias(PostgreSqlListingModerationIntentStore::class, ListingModerationIntentStore::class);
        $this->app->singleton(PostgreSqlListingModerationIntentTransaction::class);
        $this->app->alias(PostgreSqlListingModerationIntentTransaction::class, ListingModerationIntentTransaction::class);
        $this->app->singleton(OwnerListingModerationReaderV1::class);
        $this->app->alias(OwnerListingModerationReaderV1::class, ListingModerationReaderV1::class);
        $this->app->singleton(OwnerListingContactabilityReaderV1::class);
        $this->app->alias(OwnerListingContactabilityReaderV1::class, ListingContactabilityReaderV1::class);
        $this->app->singleton(OwnerListingModerationCommandGatewayV1::class);
        $this->app->alias(OwnerListingModerationCommandGatewayV1::class, ListingModerationCommandGatewayV1::class);
        $this->app->singleton(PropertyLifecycleWorkflow::class);
        $this->app->singleton(PropertyLifecycleWorkflowMapper::class);
        $this->app->singleton(PostgreSqlPropertyLifecycleWorkflowRepository::class);
        $this->app->alias(PostgreSqlPropertyLifecycleWorkflowRepository::class, PropertyLifecycleWorkflowStore::class);
        $this->app->singleton(ReservationLifecycleWorkflow::class);
        $this->app->singleton(ReservationLifecycleWorkflowMapper::class);
        $this->app->singleton(PostgreSqlReservationLifecycleWorkflowRepository::class);
        $this->app->alias(PostgreSqlReservationLifecycleWorkflowRepository::class, ReservationLifecycleWorkflowStore::class);
        $this->app->singleton(LeadLifecycleWorkflow::class);
        $this->app->singleton(LeadLifecycleWorkflowMapper::class);
        $this->app->singleton(PostgreSqlLeadLifecycleWorkflowRepository::class);
        $this->app->alias(PostgreSqlLeadLifecycleWorkflowRepository::class, LeadLifecycleWorkflowStore::class);
        $this->app->singleton(ProfessionalStatusWorkflow::class);
        $this->app->singleton(ProfessionalStatusWorkflowMapper::class);
        $this->app->singleton(PostgreSqlProfessionalStatusWorkflowRepository::class);
        $this->app->alias(PostgreSqlProfessionalStatusWorkflowRepository::class, ProfessionalStatusWorkflowStore::class);
        $this->app->singleton(ProfessionalMandateOwnerSourceMapper::class);
        $this->app->singleton(PostgreSqlProfessionalMandateOwnerSource::class);
        $this->app->alias(PostgreSqlProfessionalMandateOwnerSource::class, ProfessionalMandateOwnerSource::class);
        $this->app->singleton(OwnerProfessionalMandateResolverV1::class);
        $this->app->alias(OwnerProfessionalMandateResolverV1::class, ProfessionalMandateResolverV1::class);
        $this->app->singleton(OwnerProfessionalPublicStatusReaderV1::class);
        $this->app->alias(OwnerProfessionalPublicStatusReaderV1::class, ProfessionalPublicStatusReaderV1::class);
        $this->app->singleton(MediaItemLifecycleWorkflow::class);
        $this->app->singleton(MediaItemLifecycleWorkflowMapper::class);
        $this->app->singleton(PostgreSqlMediaItemLifecycleWorkflowRepository::class);
        $this->app->alias(PostgreSqlMediaItemLifecycleWorkflowRepository::class, MediaItemLifecycleWorkflowStore::class);
        $this->app->singleton(AdministrativeActionLifecycleWorkflow::class);
        $this->app->singleton(AdministrativeActionLifecycleWorkflowMapper::class);
        $this->app->singleton(AdministrativeActionEnrollmentCanonicalizer::class);
        $this->app->singleton(PostgreSqlAdministrativeActionLifecycleAtomicPersistenceTransaction::class);
        $this->app->singleton(
            PostgreSqlAdministrativeActionLifecycleRepository::class,
            static fn (Application $app): PostgreSqlAdministrativeActionLifecycleRepository => new PostgreSqlAdministrativeActionLifecycleRepository(
                $app->make(PDO::class),
                $app->make(AdministrativeActionLifecycleWorkflowMapper::class),
                $app->make(AdministrativeActionEnrollmentCanonicalizer::class),
                $app->make(PostgreSqlAdministrativeActionLifecycleAtomicPersistenceTransaction::class),
            ),
        );
        $this->app->alias(PostgreSqlAdministrativeActionLifecycleRepository::class, AdministrativeActionLifecycleWorkflowStore::class);
        $this->app->singleton(PlaceLifecycleWorkflow::class);
        $this->app->singleton(PlaceLifecycleWorkflowMapper::class);
        $this->app->singleton(PostgreSqlPlaceLifecycleWorkflowStore::class);
        $this->app->alias(PostgreSqlPlaceLifecycleWorkflowStore::class, PlaceLifecycleWorkflowStore::class);
        $this->app->singleton(AccountPersistenceMapper::class);
        $this->app->singleton(PostgreSqlAccountRepository::class);
        $this->app->alias(PostgreSqlAccountRepository::class, AccountRegistry::class);
        $this->app->singleton(AccountStatusWorkflow::class);
        $this->app->singleton(AccountStatusWorkflowMapper::class);
        $this->app->singleton(PostgreSqlAccountStatusWorkflowStore::class);
        $this->app->alias(PostgreSqlAccountStatusWorkflowStore::class, AccountStatusWorkflowStore::class);
        $this->app->singleton(PostgreSqlAccountStatusOrchestrationTransaction::class);
        $this->app->alias(PostgreSqlAccountStatusOrchestrationTransaction::class, AccountStatusOrchestrationTransaction::class);
        $this->app->singleton(DeterministicAccountStatusOrchestrator::class);
        $this->app->alias(DeterministicAccountStatusOrchestrator::class, AccountStatusOrchestrator::class);
        $this->app->singleton(AccountStatusTransportSerializer::class);
        $this->app->singleton(DeterministicAccountStatusEventRouter::class);
        $this->app->alias(DeterministicAccountStatusEventRouter::class, AccountStatusEventRouter::class);
        $this->app->singleton(AccountStatusDeliveryConsumer::class);
        $this->app->singleton(AccountStatusEventCatalog::class);
        $this->app->alias(PostgreSqlAggregateOutboxTransaction::class, AccountStatusAtomicTransaction::class);
        $this->app->singleton(AccountStatusAtomicEventOrchestrator::class);
        $this->app->singleton(PostgreSqlPlaceMergeContextInspector::class);
        $this->app->alias(PostgreSqlPlaceMergeContextInspector::class, PlaceMergeContextInspector::class);
        $this->app->singleton(DeterministicPlaceMergeReplayClassifier::class);
        $this->app->alias(DeterministicPlaceMergeReplayClassifier::class, PlaceMergeReplayClassifier::class);
        $this->app->singleton(PlaceLifecycleOrchestrator::class);
        $this->app->singleton(PlaceLifecycleEventCatalog::class);
        $this->app->singleton(PlaceLifecycleAtomicEventOrchestrator::class);
        $this->app->singleton(PlaceLifecycleTransportSerializer::class);
        $this->app->singleton(PostgreSqlPlaceLifecycleInboxRepository::class);
        $this->app->alias(PostgreSqlPlaceLifecycleInboxRepository::class, PlaceLifecycleInboxStore::class);
        $this->app->singleton(DurablePlaceLifecycleEventRouter::class);
        $this->app->alias(DurablePlaceLifecycleEventRouter::class, PlaceLifecycleEventRouter::class);
        $this->app->singleton(PlaceLifecycleDeliveryConsumptionPolicy::class);
        $this->app->singleton(PlaceLifecycleDeliveryConsumer::class);
        $this->app->singleton(AdministrativeActionTransitionContextMapper::class);
        $this->app->singleton(PostgreSqlAdministrativeActionContextualTransitionRepository::class);
        $this->app->alias(PostgreSqlAdministrativeActionContextualTransitionRepository::class, AdministrativeActionContextualTransitionStore::class);
        $this->app->singleton(PostgreSqlAdministrativeActionContextualReplayInspector::class);
        $this->app->alias(PostgreSqlAdministrativeActionContextualReplayInspector::class, AdministrativeActionContextualReplayInspector::class);
        $this->app->singleton(AdministrativeActionReplayPolicy::class);
        $this->app->singleton(DeterministicAdministrativeActionLifecycleOrchestrator::class);
        $this->app->alias(DeterministicAdministrativeActionLifecycleOrchestrator::class, AdministrativeActionLifecycleOrchestrator::class);
        $this->app->singleton(AdministrativeActionLifecycleTransportSerializer::class);
        $this->app->singleton(PostgreSqlAdministrativeActionLifecycleInboxRepository::class);
        $this->app->alias(PostgreSqlAdministrativeActionLifecycleInboxRepository::class, AdministrativeActionLifecycleInboxStore::class);
        $this->app->singleton(DurableAdministrativeActionLifecycleEventRouter::class);
        $this->app->alias(DurableAdministrativeActionLifecycleEventRouter::class, AdministrativeActionLifecycleEventRouter::class);
        $this->app->singleton(AdministrativeActionLifecycleDeliveryConsumptionPolicy::class);
        $this->app->singleton(AdministrativeActionLifecycleDeliveryConsumer::class);
        $this->app->singleton(AdministrativeActionLifecycleEventCatalog::class);
        $this->app->singleton(AdministrativeActionLifecycleAtomicEventOrchestrator::class);
        $this->app->singleton(MediaItemLifecycleContextMapper::class);
        $this->app->singleton(PostgreSqlMediaItemLifecycleContextualTransitionRepository::class);
        $this->app->alias(PostgreSqlMediaItemLifecycleContextualTransitionRepository::class, MediaItemLifecycleContextualTransitionStore::class);
        $this->app->singleton(PostgreSqlMediaItemLifecycleContextualReplayInspector::class);
        $this->app->alias(PostgreSqlMediaItemLifecycleContextualReplayInspector::class, MediaItemLifecycleContextualReplayInspector::class);
        $this->app->singleton(MediaItemLifecycleReplayPolicy::class);
        $this->app->singleton(DeterministicMediaItemLifecycleOrchestrator::class);
        $this->app->alias(DeterministicMediaItemLifecycleOrchestrator::class, MediaItemLifecycleOrchestrator::class);
        $this->app->singleton(MediaItemLifecycleTransportSerializer::class);
        $this->app->singleton(PostgreSqlMediaItemLifecycleInboxRepository::class);
        $this->app->alias(PostgreSqlMediaItemLifecycleInboxRepository::class, MediaItemLifecycleInboxStore::class);
        $this->app->singleton(DurableMediaItemLifecycleEventRouter::class);
        $this->app->alias(DurableMediaItemLifecycleEventRouter::class, MediaItemLifecycleEventRouter::class);
        $this->app->singleton(MediaItemLifecycleDeliveryConsumptionPolicy::class);
        $this->app->singleton(MediaItemLifecycleDeliveryConsumer::class);
        $this->app->singleton(MediaItemLifecycleAtomicEventOrchestrator::class);
        $this->app->singleton(ProfessionalStatusContextMapper::class);
        $this->app->singleton(PostgreSqlProfessionalStatusContextualTransitionRepository::class);
        $this->app->alias(PostgreSqlProfessionalStatusContextualTransitionRepository::class, ProfessionalStatusContextualTransitionStore::class);
        $this->app->singleton(PostgreSqlProfessionalStatusContextualReplayInspector::class);
        $this->app->alias(PostgreSqlProfessionalStatusContextualReplayInspector::class, ProfessionalStatusContextualReplayInspector::class);
        $this->app->singleton(ProfessionalStatusReplayPolicy::class);
        $this->app->singleton(DeterministicProfessionalStatusOrchestrator::class);
        $this->app->alias(DeterministicProfessionalStatusOrchestrator::class, ProfessionalStatusOrchestrator::class);
        $this->app->singleton(ProfessionalStatusTransportSerializer::class);
        $this->app->singleton(PostgreSqlProfessionalStatusInboxRepository::class);
        $this->app->alias(PostgreSqlProfessionalStatusInboxRepository::class, ProfessionalStatusInboxStore::class);
        $this->app->singleton(DurableProfessionalStatusEventRouter::class);
        $this->app->alias(DurableProfessionalStatusEventRouter::class, ProfessionalStatusEventRouter::class);
        $this->app->singleton(ProfessionalStatusDeliveryConsumptionPolicy::class);
        $this->app->singleton(ProfessionalStatusDeliveryConsumer::class);
        $this->app->singleton(ProfessionalStatusAtomicEventOrchestrator::class);
        $this->app->singleton(LeadLifecycleContextMapper::class);
        $this->app->singleton(PostgreSqlLeadLifecycleContextualTransitionRepository::class);
        $this->app->alias(PostgreSqlLeadLifecycleContextualTransitionRepository::class, LeadLifecycleContextualTransitionStore::class);
        $this->app->singleton(PostgreSqlLeadLifecycleContextualReplayInspector::class);
        $this->app->alias(PostgreSqlLeadLifecycleContextualReplayInspector::class, LeadLifecycleContextualReplayInspector::class);
        $this->app->singleton(DeterministicLeadLifecycleOrchestrator::class);
        $this->app->alias(DeterministicLeadLifecycleOrchestrator::class, LeadLifecycleOrchestrator::class);
        $this->app->singleton(LeadLifecycleAtomicEventOrchestrator::class);
        $this->app->singleton(LeadLifecycleTransportSerializer::class);
        $this->app->singleton(PostgreSqlLeadLifecycleInboxRepository::class);
        $this->app->alias(PostgreSqlLeadLifecycleInboxRepository::class, LeadLifecycleInboxStore::class);
        $this->app->singleton(DurableLeadLifecycleEventRouter::class);
        $this->app->alias(DurableLeadLifecycleEventRouter::class, LeadLifecycleEventRouter::class);
        $this->app->singleton(LeadLifecycleDeliveryConsumptionPolicy::class);
        $this->app->singleton(LeadLifecycleDeliveryConsumer::class);
        $this->app->singleton(LeadEligibilitySourceDataMapper::class);
        $this->app->singleton(PostgreSqlLeadEligibilityDecisionStore::class);
        $this->app->alias(PostgreSqlLeadEligibilityDecisionStore::class, LeadEligibilityDecisionMaterializer::class);
        $this->app->alias(PostgreSqlLeadEligibilityDecisionStore::class, LeadEligibilitySourceDataReader::class);
        $this->app->singleton(MaterializedListingCatalog::class);
        $this->app->alias(MaterializedListingCatalog::class, ListingCatalog::class);
        $this->app->singleton(MaterializedAdvertiserCatalog::class);
        $this->app->alias(MaterializedAdvertiserCatalog::class, AdvertiserCatalog::class);
        $this->app->singleton(DeterministicReservationLifecycleEventOrchestrator::class);
        $this->app->alias(DeterministicReservationLifecycleEventOrchestrator::class, ReservationLifecycleEventOrchestrator::class);
        $this->app->singleton(ReservationLifecycleTransportSerializer::class);
        $this->app->singleton(PostgreSqlReservationLifecycleInboxRepository::class);
        $this->app->alias(PostgreSqlReservationLifecycleInboxRepository::class, ReservationLifecycleInboxStore::class);
        $this->app->singleton(DeterministicReservationLifecycleEventRouter::class);
        $this->app->alias(DeterministicReservationLifecycleEventRouter::class, ReservationLifecycleEventRouterPort::class);
        $this->app->singleton(ReservationLifecycleDeliveryConsumptionPolicy::class);
        $this->app->singleton(ReservationLifecycleDeliveryConsumer::class);
        $this->app->singleton(ReservationLifecycleAtomicEventOrchestrator::class);
        $this->app->singleton(DeterministicPropertyLifecycleOrchestrator::class);
        $this->app->alias(DeterministicPropertyLifecycleOrchestrator::class, PropertyLifecycleOrchestrator::class);
        $this->app->singleton(PropertyLifecycleEventSerializer::class);
        $this->app->singleton(PostgreSqlPropertyLifecycleEventInbox::class);
        $this->app->alias(PostgreSqlPropertyLifecycleEventInbox::class, PropertyLifecycleEventDestination::class);
        $this->app->singleton(DurablePropertyLifecycleEventRouter::class);
        $this->app->alias(DurablePropertyLifecycleEventRouter::class, PropertyLifecycleEventRouter::class);
        $this->app->singleton(PropertyLifecycleEventDeliveryConsumer::class);
        $this->app->singleton(PropertyLifecycleEventCatalog::class);
        $this->app->singleton(AtomicPropertyLifecycleEventOrchestrator::class);
        $this->app->alias(AtomicPropertyLifecycleEventOrchestrator::class, PropertyLifecycleEventOrchestrator::class);
        $this->app->singleton(DeterministicListingPublicationOrchestrator::class);
        $this->app->alias(DeterministicListingPublicationOrchestrator::class, ListingPublicationOrchestrator::class);
        $this->app->singleton(ListingPublicationEventSerializer::class);
        $this->app->singleton(PostgreSqlListingPublicationEventInbox::class);
        $this->app->alias(PostgreSqlListingPublicationEventInbox::class, ListingPublicationEventDestination::class);
        $this->app->singleton(DurableListingPublicationEventRouter::class);
        $this->app->alias(DurableListingPublicationEventRouter::class, ListingPublicationEventRouter::class);
        $this->app->singleton(ListingPublicationEventDeliveryConsumer::class);
        $this->app->singleton(ListingPublicationEventCatalog::class);
        $this->app->singleton(PublicProjectionDeliveryEventCatalog::class);
        $this->app->singleton(PublicProjectionDeliveryCatalogMessageFactory::class);
        $this->app->singleton(AtomicListingPublicationEventOrchestrator::class);
        $this->app->alias(AtomicListingPublicationEventOrchestrator::class, ListingPublicationEventOrchestrator::class);
        $this->app->bind(PublicGeographyDecisionReader::class, PostgreSqlPublicGeographyReader::class);
        $this->app->bind(PublicMediaDecisionReader::class, PostgreSqlPublicMediaReader::class);
        $this->app->bind(ActiveGenerationReader::class, PostgreSqlActiveGenerationReader::class);
        $this->app->bind(DecisionTimeReader::class, ContentSeoSnapshotDecisionTimeReader::class);

        $this->app->bind(InspectablePublicListingProjectionSource::class, CertifiedPublicListingProjectionSource::class);
        $this->app->bind(PublicListingProjectionSource::class, CertifiedPublicListingProjectionSource::class);
        $this->app->bind(MediaCollectionPropertyResolver::class, RegistryMediaCollectionPropertyResolver::class);
        $this->app->bind(PropertyListingsResolver::class, PostgreSqlPropertyListingsResolver::class);
        $this->app->bind(MultiTargetPropagationStrategy::class, PagedMultiTargetPropagationStrategy::class);
        $this->app->bind(PublicProjectionSourceLookup::class, CertifiedPublicProjectionSourceLookup::class);

        $this->app->singleton(PostgreSqlPublicListingProjectionStore::class);
        $this->app->bind(PublicListingProjectionWriter::class, PostgreSqlPublicListingProjectionStore::class);
        $this->app->bind(PublicListingQuery::class, PostgreSqlPublicListingProjectionStore::class);
        $this->app->bind(PublicProjectionGenerationValidator::class, PostgreSqlPublicProjectionGenerationValidator::class);
        $this->app->bind(PublicProjectionGenerationManager::class, PostgreSqlPublicProjectionGenerationManager::class);
        $this->app->bind(PublicProjectionRebuildEnumerator::class, PostgreSqlPublicProjectionRebuildEnumerator::class);
        $this->app->bind(InspectablePublicProjectionCandidateFactory::class, CertifiedPublicProjectionCandidateFactory::class);
        $this->app->bind(PublicProjectionCandidateFactory::class, CertifiedPublicProjectionCandidateFactory::class);

        $this->app->bind(PublicProjectionUpdateExecutor::class, PublicListingProjectionUpdaterExecutor::class);
        $this->app->bind(PublicProjectionDeliveryConsumer::class, PublicProjectionUpdaterConsumer::class);

        $this->app->bind(PublicProjectionOutboxReader::class, PostgreSqlPublicProjectionOutboxReader::class);
        $this->app->bind(PublicProjectionOutboxWriter::class, PostgreSqlPublicProjectionOutboxWriter::class);
        $this->app->bind(PublicProjectionOutboxRoutedWriterV1::class, PostgreSqlPublicProjectionOutboxWriter::class);
        $this->app->bind(PublicProjectionOutboxClaimManager::class, PostgreSqlPublicProjectionOutboxClaimManager::class);
        $this->app->bind(PublicProjectionDeliveryClock::class, SystemPublicProjectionDeliveryClock::class);
        $this->app->singleton(PublicProjectionOutboxConsumerId::class, static fn (): PublicProjectionOutboxConsumerId => PublicProjectionOutboxConsumerId::fromString((string) config('public_projection.delivery.consumer_id')));
        $this->app->singleton(PublicProjectionDeliveryWorkerId::class, static fn (): PublicProjectionDeliveryWorkerId => PublicProjectionDeliveryWorkerId::fromString((string) config('public_projection.delivery.worker_id')));
        $this->app->singleton(PublicProjectionOutboxRetryPolicy::class, static fn (): PublicProjectionOutboxRetryPolicy => new PublicProjectionDeterministicRetryPolicy(
            (int) config('public_projection.delivery.maximum_attempts'),
            new PublicProjectionFixedBackoff((int) config('public_projection.delivery.retry_delay_seconds')),
        ));
        $this->app->singleton(PublicProjectionDeliveryConsumerRegistry::class, function (Application $app): PublicProjectionDeliveryConsumerRegistry {
            $consumerId = $app->make(PublicProjectionOutboxConsumerId::class);
            $consumer = $app->make(PublicProjectionDeliveryConsumer::class);
            $listingPublicationConsumer = $app->make(ListingPublicationEventDeliveryConsumer::class);
            $propertyLifecycleConsumer = $app->make(PropertyLifecycleEventDeliveryConsumer::class);
            $reservationLifecycleConsumer = $app->make(ReservationLifecycleDeliveryConsumer::class);
            $leadLifecycleConsumer = $app->make(LeadLifecycleDeliveryConsumer::class);
            $professionalStatusConsumer = $app->make(ProfessionalStatusDeliveryConsumer::class);
            $mediaItemLifecycleConsumer = $app->make(MediaItemLifecycleDeliveryConsumer::class);
            $administrativeActionLifecycleConsumer = $app->make(AdministrativeActionLifecycleDeliveryConsumer::class);
            $placeLifecycleConsumer = $app->make(PlaceLifecycleDeliveryConsumer::class);
            $accountStatusConsumer = $app->make(AccountStatusDeliveryConsumer::class);
            $registrations = [];
            foreach (['listing.reconstruction.requested', 'property.reconstruction.requested', 'media.reconstruction.requested', 'search.reconstruction.requested', 'content_seo.reconstruction.requested'] as $eventType) {
                $registrations[] = new PublicProjectionDeliveryConsumerRegistration(
                    $consumerId,
                    PublicProjectionDeliveryEventType::fromString($eventType),
                    PublicProjectionDeliveryPayloadVersion::fromInt(1),
                    $consumer,
                );
            }
            foreach (ListingPublicationEventType::cases() as $eventType) {
                $registrations[] = new PublicProjectionDeliveryConsumerRegistration(
                    $consumerId,
                    PublicProjectionDeliveryEventType::fromString($eventType->value),
                    PublicProjectionDeliveryPayloadVersion::fromInt(1),
                    $listingPublicationConsumer,
                );
            }
            foreach (PropertyLifecycleEventType::cases() as $eventType) {
                $registrations[] = new PublicProjectionDeliveryConsumerRegistration(
                    $consumerId,
                    PublicProjectionDeliveryEventType::fromString($eventType->value),
                    PublicProjectionDeliveryPayloadVersion::fromInt(1),
                    $propertyLifecycleConsumer,
                );
            }
            foreach (ReservationLifecycleEventType::cases() as $eventType) {
                $registrations[] = new PublicProjectionDeliveryConsumerRegistration(
                    $consumerId,
                    PublicProjectionDeliveryEventType::fromString($eventType->value),
                    PublicProjectionDeliveryPayloadVersion::fromInt(1),
                    $reservationLifecycleConsumer,
                );
            }
            foreach (LeadLifecycleEventType::cases() as $eventType) {
                $registrations[] = new PublicProjectionDeliveryConsumerRegistration(
                    $consumerId,
                    PublicProjectionDeliveryEventType::fromString($eventType->value),
                    PublicProjectionDeliveryPayloadVersion::fromInt(1),
                    $leadLifecycleConsumer,
                );
            }
            foreach (ProfessionalStatusEventType::cases() as $eventType) {
                $registrations[] = new PublicProjectionDeliveryConsumerRegistration(
                    $consumerId,
                    PublicProjectionDeliveryEventType::fromString($eventType->value),
                    PublicProjectionDeliveryPayloadVersion::fromInt(1),
                    $professionalStatusConsumer,
                );
            }
            foreach (MediaItemLifecycleEventType::cases() as $eventType) {
                $registrations[] = new PublicProjectionDeliveryConsumerRegistration(
                    $consumerId,
                    PublicProjectionDeliveryEventType::fromString($eventType->value),
                    PublicProjectionDeliveryPayloadVersion::fromInt(1),
                    $mediaItemLifecycleConsumer,
                );
            }
            foreach (AdministrativeActionLifecycleEventType::cases() as $eventType) {
                $registrations[] = new PublicProjectionDeliveryConsumerRegistration(
                    $consumerId,
                    PublicProjectionDeliveryEventType::fromString($eventType->value),
                    PublicProjectionDeliveryPayloadVersion::fromInt(1),
                    $administrativeActionLifecycleConsumer,
                );
            }
            foreach (PlaceLifecycleEventType::cases() as $eventType) {
                $registrations[] = new PublicProjectionDeliveryConsumerRegistration(
                    $consumerId,
                    PublicProjectionDeliveryEventType::fromString($eventType->value),
                    PublicProjectionDeliveryPayloadVersion::fromInt(1),
                    $placeLifecycleConsumer,
                );
            }
            foreach (AccountStatusEventType::cases() as $eventType) {
                $registrations[] = new PublicProjectionDeliveryConsumerRegistration(
                    $consumerId,
                    PublicProjectionDeliveryEventType::fromString($eventType->value),
                    PublicProjectionDeliveryPayloadVersion::fromInt(1),
                    $accountStatusConsumer,
                    PublicProjectionDeliveryMode::RoutedV1,
                );
            }

            return new PublicProjectionDeliveryConsumerRegistry($registrations);
        });
        $this->app->singleton(PublicProjectionDeliveryWorker::class, static fn (Application $app): PublicProjectionDeliveryWorker => new PublicProjectionDeliveryWorker(
            $app->make(PublicProjectionOutboxReader::class),
            $app->make(PublicProjectionOutboxClaimManager::class),
            $app->make(PublicProjectionOutboxWriter::class),
            $app->make(PublicProjectionDeliveryConsumerRegistry::class),
            $app->make(PublicProjectionOutboxRetryPolicy::class),
            $app->make(PublicProjectionDeliveryClock::class),
            $app->make(PublicProjectionDeliveryWorkerId::class),
            (int) config('public_projection.delivery.batch_size'),
            (int) config('public_projection.delivery.lease_seconds'),
        ));

        $this->app->singleton(RuntimeHealthInspector::class, function (Application $app): RuntimeHealthInspector {
            return new DeterministicRuntimeHealthInspector(PublicProjectionRuntimeRequirements::certified(), [
                $this->registration($app, RuntimeHealthComponent::ProjectionSourceLookup, PublicProjectionSourceLookup::class),
                $this->registration($app, RuntimeHealthComponent::ProjectionRuntimeSource, InspectablePublicListingProjectionSource::class),
                $this->registration($app, RuntimeHealthComponent::PublicGeographySource, PublicGeographyDecisionReader::class),
                $this->registration($app, RuntimeHealthComponent::PublicMediaSource, PublicMediaDecisionReader::class),
                $this->registration($app, RuntimeHealthComponent::MediaCollectionPropertyResolver, MediaCollectionPropertyResolver::class),
                $this->registration($app, RuntimeHealthComponent::MultiTargetStrategy, MultiTargetPropagationStrategy::class),
                $this->registration($app, RuntimeHealthComponent::ProjectionUpdater, PublicProjectionUpdateExecutor::class),
                $this->registration($app, RuntimeHealthComponent::ProjectionStore, PublicListingProjectionWriter::class),
                $this->registration($app, RuntimeHealthComponent::RebuildEnumerator, PublicProjectionRebuildEnumerator::class),
                $this->registration($app, RuntimeHealthComponent::DeliveryConsumer, PublicProjectionDeliveryConsumer::class),
                $this->registration($app, RuntimeHealthComponent::HistoricalRedirectResolver, HistoricalRedirectResolver::class),
                $this->registration($app, RuntimeHealthComponent::HistoricalCanonicalQualifier, HistoricalCanonicalQualifier::class),
                $this->registration($app, RuntimeHealthComponent::ListingPublicationWorkflow, ListingPublicationWorkflow::class),
                $this->registration($app, RuntimeHealthComponent::ListingPublicationWorkflowStore, ListingPublicationWorkflowStore::class),
                $this->registration($app, RuntimeHealthComponent::ListingPublicationEventDestination, ListingPublicationEventDestination::class),
                $this->registration($app, RuntimeHealthComponent::ListingPublicationEventRouter, ListingPublicationEventRouter::class),
                $this->registration($app, RuntimeHealthComponent::ListingPublicationEventOrchestrator, ListingPublicationEventOrchestrator::class),
                $this->registration($app, RuntimeHealthComponent::PropertyLifecycleWorkflow, PropertyLifecycleWorkflow::class),
                $this->registration($app, RuntimeHealthComponent::PropertyLifecycleWorkflowStore, PropertyLifecycleWorkflowStore::class),
                $this->registration($app, RuntimeHealthComponent::PropertyLifecycleOrchestrator, PropertyLifecycleOrchestrator::class),
                $this->registration($app, RuntimeHealthComponent::PropertyLifecycleEventDestination, PropertyLifecycleEventDestination::class),
                $this->registration($app, RuntimeHealthComponent::PropertyLifecycleEventRouter, PropertyLifecycleEventRouter::class),
                $this->registration($app, RuntimeHealthComponent::PropertyLifecycleEventOrchestrator, PropertyLifecycleEventOrchestrator::class),
                $this->registration($app, RuntimeHealthComponent::ReservationLifecycleWorkflow, ReservationLifecycleWorkflow::class),
                $this->registration($app, RuntimeHealthComponent::ReservationLifecycleWorkflowStore, ReservationLifecycleWorkflowStore::class),
                $this->registration($app, RuntimeHealthComponent::ReservationLifecycleInboxStore, ReservationLifecycleInboxStore::class),
                $this->registration($app, RuntimeHealthComponent::ReservationLifecycleEventRouter, ReservationLifecycleEventRouterPort::class),
                $this->registration($app, RuntimeHealthComponent::LeadLifecycleWorkflow, LeadLifecycleWorkflow::class),
                $this->registration($app, RuntimeHealthComponent::LeadLifecycleWorkflowStore, LeadLifecycleWorkflowStore::class),
                $this->registration($app, RuntimeHealthComponent::LeadLifecycleOrchestrator, LeadLifecycleOrchestrator::class),
                $this->registration($app, RuntimeHealthComponent::LeadLifecycleInboxStore, LeadLifecycleInboxStore::class),
                $this->registration($app, RuntimeHealthComponent::LeadLifecycleEventRouter, LeadLifecycleEventRouter::class),
                $this->registration($app, RuntimeHealthComponent::LeadEligibilityDecisionMaterializer, LeadEligibilityDecisionMaterializer::class),
                $this->registration($app, RuntimeHealthComponent::ListingCatalog, ListingCatalog::class),
                $this->registration($app, RuntimeHealthComponent::AdvertiserCatalog, AdvertiserCatalog::class),
                $this->registration($app, RuntimeHealthComponent::ProfessionalStatusWorkflow, ProfessionalStatusWorkflow::class),
                $this->registration($app, RuntimeHealthComponent::ProfessionalStatusWorkflowStore, ProfessionalStatusWorkflowStore::class),
                $this->registration($app, RuntimeHealthComponent::ProfessionalStatusOrchestrator, ProfessionalStatusOrchestrator::class),
                $this->registration($app, RuntimeHealthComponent::ProfessionalStatusInboxStore, ProfessionalStatusInboxStore::class),
                $this->registration($app, RuntimeHealthComponent::ProfessionalStatusEventRouter, ProfessionalStatusEventRouter::class),
                $this->registration($app, RuntimeHealthComponent::ProfessionalMandateResolver, ProfessionalMandateResolverV1::class),
                $this->registration($app, RuntimeHealthComponent::ProfessionalPublicStatusReader, ProfessionalPublicStatusReaderV1::class),
                $this->registration($app, RuntimeHealthComponent::MediaItemLifecycleWorkflow, MediaItemLifecycleWorkflow::class),
                $this->registration($app, RuntimeHealthComponent::MediaItemLifecycleWorkflowStore, MediaItemLifecycleWorkflowStore::class),
                $this->registration($app, RuntimeHealthComponent::MediaItemLifecycleOrchestrator, MediaItemLifecycleOrchestrator::class),
                $this->registration($app, RuntimeHealthComponent::MediaItemLifecycleInboxStore, MediaItemLifecycleInboxStore::class),
                $this->registration($app, RuntimeHealthComponent::MediaItemLifecycleEventRouter, MediaItemLifecycleEventRouter::class),
                $this->registration($app, RuntimeHealthComponent::AdministrativeActionLifecycleWorkflow, AdministrativeActionLifecycleWorkflow::class),
                $this->registration($app, RuntimeHealthComponent::AdministrativeActionLifecycleWorkflowStore, AdministrativeActionLifecycleWorkflowStore::class),
                $this->registration($app, RuntimeHealthComponent::AdministrativeActionLifecycleOrchestrator, AdministrativeActionLifecycleOrchestrator::class),
                $this->registration($app, RuntimeHealthComponent::AdministrativeActionLifecycleInboxStore, AdministrativeActionLifecycleInboxStore::class),
                $this->registration($app, RuntimeHealthComponent::AdministrativeActionLifecycleEventRouter, AdministrativeActionLifecycleEventRouter::class),
                $this->registration($app, RuntimeHealthComponent::PlaceLifecycleWorkflow, PlaceLifecycleWorkflow::class),
                $this->registration($app, RuntimeHealthComponent::PlaceLifecycleWorkflowStore, PlaceLifecycleWorkflowStore::class),
                $this->registration($app, RuntimeHealthComponent::PlaceLifecycleInboxStore, PlaceLifecycleInboxStore::class),
                $this->registration($app, RuntimeHealthComponent::PlaceLifecycleEventRouter, PlaceLifecycleEventRouter::class),
                $this->registration($app, RuntimeHealthComponent::PlaceLifecycleDeliveryConsumer, PlaceLifecycleDeliveryConsumer::class),
                $this->registration($app, RuntimeHealthComponent::AccountStatusWorkflow, AccountStatusWorkflow::class),
                $this->registration($app, RuntimeHealthComponent::AccountStatusWorkflowStore, AccountStatusWorkflowStore::class),
                $this->registration($app, RuntimeHealthComponent::AccountStatusOrchestrator, AccountStatusOrchestrator::class),
            ]);
        });
    }

    /** @param class-string $contract */
    private function registration(Application $app, RuntimeHealthComponent $component, string $contract): RuntimeHealthRegistration
    {
        return new RuntimeHealthRegistration($component, $app->bound($contract), $app->make($contract));
    }
}
