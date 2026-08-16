<?php

use App\Providers\AddressIdentityServiceProvider;
use App\Providers\ActiveGenerationBootstrapServiceProvider;
use App\Providers\AdministrationAuditPublicAppendServiceProvider;
use App\Providers\AdministrationConsoleHttpServiceProvider;
use App\Providers\AdministrationConsoleOwnerReaderServiceProvider;
use App\Providers\AdministrationConsoleRuntimeServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AuthoringPublicFactHandoffServiceProvider;
use App\Providers\AuthoringDraftResumeServiceProvider;
use App\Providers\BusinessYearAuthorityServiceProvider;
use App\Providers\ContactsLeadsAntiAbuseOwnerReaderServiceProvider;
use App\Providers\ContactsLeadsAntiAbuseOwnerSourceRuntimeReadServiceProvider;
use App\Providers\ContactsLeadsAntiAbuseOwnerSourceRuntimeServiceProvider;
use App\Providers\ContactsLeadsConsentOwnerReaderServiceProvider;
use App\Providers\ContactsLeadsConsentOwnerSourceRuntimeReadServiceProvider;
use App\Providers\ContactsLeadsConsentOwnerSourceRuntimeServiceProvider;
use App\Providers\ContentSeoHttpServiceProvider;
use App\Providers\ContentSeoOwnerReaderServiceProvider;
use App\Providers\ContentSeoRuntimeServiceProvider;
use App\Providers\ContentSeoSnapshotMaterializationServiceProvider;
use App\Providers\ExperienceAcceptanceHttpServiceProvider;
use App\Providers\ExperienceAcceptanceOwnerReaderServiceProvider;
use App\Providers\ExperienceAcceptanceRuntimeServiceProvider;
use App\Providers\GeographicPlaceCatalogServiceProvider;
use App\Providers\GeographyPlacePersistenceServiceProvider;
use App\Providers\GeographySelectionServiceProvider;
use App\Providers\IdentityAccessEventOutboxServiceProvider;
use App\Providers\IdentityAccessHttpServiceProvider;
use App\Providers\IdentityAccessOrchestrationServiceProvider;
use App\Providers\IdentityAccessRuntimeServiceProvider;
use App\Providers\LegacyMigrationHttpServiceProvider;
use App\Providers\LegacyMigrationOwnerReaderServiceProvider;
use App\Providers\LegacyMigrationRuntimeServiceProvider;
use App\Providers\ListingAuthoringRuntimeServiceProvider;
use App\Providers\ListingPublicationCommandGatewayServiceProvider;
use App\Providers\ListingRevisionAuthorityServiceProvider;
use App\Providers\MediaAssetReadinessServiceProvider;
use App\Providers\MediaAssetRuntimeServiceProvider;
use App\Providers\MediaAuthoringHttpServiceProvider;
use App\Providers\MediaBinaryStorageServiceProvider;
use App\Providers\MediaIngestionEventDeliveryServiceProvider;
use App\Providers\MediaIngestionEventOutboxServiceProvider;
use App\Providers\MediaIngestionRuntimeServiceProvider;
use App\Providers\MediaProcessingRuntimeServiceProvider;
use App\Providers\MediaQuotaRuntimeServiceProvider;
use App\Providers\MediaReadyAssetAttachmentServiceProvider;
use App\Providers\MediaUploadRuntimeServiceProvider;
use App\Providers\ModerationAtomicOperationServiceProvider;
use App\Providers\ModerationEventDeliveryServiceProvider;
use App\Providers\ModerationEventOutboxServiceProvider;
use App\Providers\ModerationHttpReadBoundariesServiceProvider;
use App\Providers\ModerationHttpServiceProvider;
use App\Providers\ModerationListingHandoffServiceProvider;
use App\Providers\ModerationOperationalAuditServiceProvider;
use App\Providers\ModerationOrchestrationServiceProvider;
use App\Providers\ModerationReportOwnerReadSourceServiceProvider;
use App\Providers\ModerationRuntimeServiceProvider;
use App\Providers\ModeratorAuthorizationServiceProvider;
use App\Providers\NotificationsHttpServiceProvider;
use App\Providers\NotificationsOwnerReaderServiceProvider;
use App\Providers\NotificationsRuntimeServiceProvider;
use App\Providers\OwnerDashboardServiceProvider;
use App\Providers\ProfessionalProfileHttpServiceProvider;
use App\Providers\ProfessionalProfileRuntimeServiceProvider;
use App\Providers\ProfessionalPublicPortfolioRuntimeServiceProvider;
use App\Providers\ProfessionalPublicProfileRuntimeServiceProvider;
use App\Providers\ProfessionalVerificationRuntimeServiceProvider;
use App\Providers\PropertyAuthoringGeographySelectionServiceProvider;
use App\Providers\PropertyAuthoringRuntimeServiceProvider;
use App\Providers\PropertyAuthoringSourceCompletenessServiceProvider;
use App\Providers\PropertyListingAuthoringHttpServiceProvider;
use App\Providers\PropertyListingAuthoringOperationsServiceProvider;
use App\Providers\PropertyListingAuthoringRuntimeServiceProvider;
use App\Providers\PublicationReviewAuthorizationServiceProvider;
use App\Providers\PublicationReviewExperienceServiceProvider;
use App\Providers\PublicationReviewQueueServiceProvider;
use App\Providers\PublicAuthoringIntegrationServiceProvider;
use App\Providers\PublicProjectionRuntimeServiceProvider;
use App\Providers\PublicPropertyPromotionServiceProvider;
use App\Providers\PublicMediaBinaryDeliveryServiceProvider;
use App\Providers\PublicMediaMaterializationServiceProvider;
use App\Providers\PublicSearchDecisionMaterializationServiceProvider;
use App\Providers\PublicSearchQueryResolutionReaderServiceProvider;
use App\Providers\PublicSearchResultsServiceProvider;
use App\Providers\ReliabilityOperationsHttpServiceProvider;
use App\Providers\ReliabilityOperationsOwnerReaderServiceProvider;
use App\Providers\ReliabilityOperationsRuntimeServiceProvider;
use App\Providers\ReservationAvailabilityOwnerReaderServiceProvider;
use App\Providers\ReservationAvailabilityOwnerSourceRuntimeReadServiceProvider;
use App\Providers\ReservationAvailabilityOwnerSourceRuntimeServiceProvider;
use App\Providers\SearchOwnerSourceRuntimeReadServiceProvider;
use App\Providers\SearchOwnerSourceRuntimeServiceProvider;
use App\Providers\SearchQueryResolutionHttpServiceProvider;
use App\Providers\SearchQueryResolutionOwnerSourceRuntimeReadServiceProvider;
use App\Providers\SearchQueryResolutionOwnerSourceRuntimeServiceProvider;
use App\Providers\SecurityComplianceHttpServiceProvider;
use App\Providers\SecurityComplianceOwnerReaderServiceProvider;
use App\Providers\SecurityComplianceRuntimeServiceProvider;

return [
    AppServiceProvider::class,
    AddressIdentityServiceProvider::class,
    ActiveGenerationBootstrapServiceProvider::class,
    AuthoringPublicFactHandoffServiceProvider::class,
    BusinessYearAuthorityServiceProvider::class,
    ExperienceAcceptanceHttpServiceProvider::class,
    ExperienceAcceptanceOwnerReaderServiceProvider::class,
    ExperienceAcceptanceRuntimeServiceProvider::class,
    GeographyPlacePersistenceServiceProvider::class,
    GeographicPlaceCatalogServiceProvider::class,
    GeographySelectionServiceProvider::class,
    ReliabilityOperationsHttpServiceProvider::class,
    ReliabilityOperationsOwnerReaderServiceProvider::class,
    ReliabilityOperationsRuntimeServiceProvider::class,
    SecurityComplianceRuntimeServiceProvider::class,
    SecurityComplianceOwnerReaderServiceProvider::class,
    SecurityComplianceHttpServiceProvider::class,
    LegacyMigrationRuntimeServiceProvider::class,
    LegacyMigrationOwnerReaderServiceProvider::class,
    LegacyMigrationHttpServiceProvider::class,
    AdministrationConsoleHttpServiceProvider::class,
    AdministrationConsoleRuntimeServiceProvider::class,
    AdministrationConsoleOwnerReaderServiceProvider::class,
    ContentSeoRuntimeServiceProvider::class,
    ContentSeoOwnerReaderServiceProvider::class,
    ContentSeoHttpServiceProvider::class,
    NotificationsRuntimeServiceProvider::class,
    NotificationsHttpServiceProvider::class,
    NotificationsOwnerReaderServiceProvider::class,
    ReservationAvailabilityOwnerSourceRuntimeServiceProvider::class,
    SearchOwnerSourceRuntimeServiceProvider::class,
    SearchOwnerSourceRuntimeReadServiceProvider::class,
    SearchQueryResolutionOwnerSourceRuntimeServiceProvider::class,
    SearchQueryResolutionOwnerSourceRuntimeReadServiceProvider::class,
    PublicSearchQueryResolutionReaderServiceProvider::class,
    PublicSearchResultsServiceProvider::class,
    SearchQueryResolutionHttpServiceProvider::class,
    ReservationAvailabilityOwnerSourceRuntimeReadServiceProvider::class,
    ReservationAvailabilityOwnerReaderServiceProvider::class,
    ContactsLeadsAntiAbuseOwnerSourceRuntimeServiceProvider::class,
    ContactsLeadsAntiAbuseOwnerSourceRuntimeReadServiceProvider::class,
    ContactsLeadsAntiAbuseOwnerReaderServiceProvider::class,
    ContactsLeadsConsentOwnerSourceRuntimeServiceProvider::class,
    ContactsLeadsConsentOwnerSourceRuntimeReadServiceProvider::class,
    ContactsLeadsConsentOwnerReaderServiceProvider::class,
    AdministrationAuditPublicAppendServiceProvider::class,
    PublicProjectionRuntimeServiceProvider::class,
    PublicSearchDecisionMaterializationServiceProvider::class,
    ContentSeoSnapshotMaterializationServiceProvider::class,
    IdentityAccessRuntimeServiceProvider::class,
    IdentityAccessOrchestrationServiceProvider::class,
    IdentityAccessEventOutboxServiceProvider::class,
    IdentityAccessHttpServiceProvider::class,
    PropertyAuthoringRuntimeServiceProvider::class,
    ListingAuthoringRuntimeServiceProvider::class,
    ListingRevisionAuthorityServiceProvider::class,
    ListingPublicationCommandGatewayServiceProvider::class,
    PropertyListingAuthoringRuntimeServiceProvider::class,
    PublicPropertyPromotionServiceProvider::class,
    PublicMediaBinaryDeliveryServiceProvider::class,
    PublicMediaMaterializationServiceProvider::class,
    PropertyListingAuthoringHttpServiceProvider::class,
    PropertyListingAuthoringOperationsServiceProvider::class,
    PublicAuthoringIntegrationServiceProvider::class,
    MediaUploadRuntimeServiceProvider::class,
    MediaAssetRuntimeServiceProvider::class,
    MediaBinaryStorageServiceProvider::class,
    MediaAssetReadinessServiceProvider::class,
    MediaReadyAssetAttachmentServiceProvider::class,
    MediaProcessingRuntimeServiceProvider::class,
    MediaQuotaRuntimeServiceProvider::class,
    MediaIngestionRuntimeServiceProvider::class,
    MediaAuthoringHttpServiceProvider::class,
    MediaIngestionEventDeliveryServiceProvider::class,
    MediaIngestionEventOutboxServiceProvider::class,
    ModerationRuntimeServiceProvider::class,
    ModerationOrchestrationServiceProvider::class,
    ModerationOperationalAuditServiceProvider::class,
    ModerationReportOwnerReadSourceServiceProvider::class,
    ModerationHttpReadBoundariesServiceProvider::class,
    ModerationHttpServiceProvider::class,
    ModerationEventDeliveryServiceProvider::class,
    ModerationAtomicOperationServiceProvider::class,
    ModerationEventOutboxServiceProvider::class,
    ModerationListingHandoffServiceProvider::class,
    ModeratorAuthorizationServiceProvider::class,
    OwnerDashboardServiceProvider::class,
    PublicationReviewQueueServiceProvider::class,
    PublicationReviewAuthorizationServiceProvider::class,
    PublicationReviewExperienceServiceProvider::class,
    ProfessionalPublicProfileRuntimeServiceProvider::class,
    ProfessionalVerificationRuntimeServiceProvider::class,
    ProfessionalPublicPortfolioRuntimeServiceProvider::class,
    ProfessionalProfileRuntimeServiceProvider::class,
    ProfessionalProfileHttpServiceProvider::class,
    PropertyAuthoringGeographySelectionServiceProvider::class,
    PropertyAuthoringSourceCompletenessServiceProvider::class,
    AuthoringDraftResumeServiceProvider::class,
];
