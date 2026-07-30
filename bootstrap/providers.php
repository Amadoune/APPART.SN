<?php

use App\Providers\AdministrationAuditPublicAppendServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\IdentityAccessEventOutboxServiceProvider;
use App\Providers\IdentityAccessHttpServiceProvider;
use App\Providers\IdentityAccessOrchestrationServiceProvider;
use App\Providers\IdentityAccessRuntimeServiceProvider;
use App\Providers\ListingAuthoringRuntimeServiceProvider;
use App\Providers\MediaAssetRuntimeServiceProvider;
use App\Providers\MediaIngestionEventDeliveryServiceProvider;
use App\Providers\MediaIngestionEventOutboxServiceProvider;
use App\Providers\MediaIngestionRuntimeServiceProvider;
use App\Providers\MediaProcessingRuntimeServiceProvider;
use App\Providers\MediaQuotaRuntimeServiceProvider;
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
use App\Providers\ProfessionalProfileHttpServiceProvider;
use App\Providers\ProfessionalProfileRuntimeServiceProvider;
use App\Providers\ProfessionalPublicPortfolioRuntimeServiceProvider;
use App\Providers\ProfessionalPublicProfileRuntimeServiceProvider;
use App\Providers\ProfessionalVerificationRuntimeServiceProvider;
use App\Providers\PropertyAuthoringRuntimeServiceProvider;
use App\Providers\PropertyListingAuthoringHttpServiceProvider;
use App\Providers\PropertyListingAuthoringOperationsServiceProvider;
use App\Providers\PropertyListingAuthoringRuntimeServiceProvider;
use App\Providers\PublicAuthoringIntegrationServiceProvider;
use App\Providers\PublicProjectionRuntimeServiceProvider;

return [
    AppServiceProvider::class,
    AdministrationAuditPublicAppendServiceProvider::class,
    PublicProjectionRuntimeServiceProvider::class,
    IdentityAccessRuntimeServiceProvider::class,
    IdentityAccessOrchestrationServiceProvider::class,
    IdentityAccessEventOutboxServiceProvider::class,
    IdentityAccessHttpServiceProvider::class,
    PropertyAuthoringRuntimeServiceProvider::class,
    ListingAuthoringRuntimeServiceProvider::class,
    PropertyListingAuthoringRuntimeServiceProvider::class,
    PropertyListingAuthoringHttpServiceProvider::class,
    PropertyListingAuthoringOperationsServiceProvider::class,
    PublicAuthoringIntegrationServiceProvider::class,
    MediaUploadRuntimeServiceProvider::class,
    MediaAssetRuntimeServiceProvider::class,
    MediaProcessingRuntimeServiceProvider::class,
    MediaQuotaRuntimeServiceProvider::class,
    MediaIngestionRuntimeServiceProvider::class,
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
    ProfessionalPublicProfileRuntimeServiceProvider::class,
    ProfessionalVerificationRuntimeServiceProvider::class,
    ProfessionalPublicPortfolioRuntimeServiceProvider::class,
    ProfessionalProfileRuntimeServiceProvider::class,
    ProfessionalProfileHttpServiceProvider::class,
];
