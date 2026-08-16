# Revised Staging Manifest after Whitespace Correction

The historical `STAGING-MANIFEST.md` remains the authoritative record of the initial 1,432-path NO GO snapshot. This revised manifest adds the correction evidence and preserves the same single ZIP exclusion.

| Path | Decision | Category | Justification |
|---|---|---|---|
| `.env.postgresql.example` | Include | E | Versionable non-secret database-isolation template |
| `.github/workflows/phase-5.9-reproducible-build.yml` | Include | D | Release engineering source/evidence |
| `app/Application/ActiveGenerationBootstrap/ActiveGenerationBootstrapState.php` | Include | A | Certified RC2 product/application source |
| `app/Application/ActiveGenerationBootstrap/BootstrapActiveProjectionGenerationCommand.php` | Include | A | Certified RC2 product/application source |
| `app/Application/ActiveGenerationBootstrap/BootstrapActiveProjectionGenerationResult.php` | Include | A | Certified RC2 product/application source |
| `app/Application/ActiveGenerationBootstrap/BootstrapActiveProjectionGenerationStatus.php` | Include | A | Certified RC2 product/application source |
| `app/Application/ActiveGenerationBootstrap/Contract/ActiveGenerationBootstrapStateReader.php` | Include | A | Certified RC2 product/application source |
| `app/Application/ActiveGenerationBootstrap/Contract/BootstrapActiveProjectionGenerationV1.php` | Include | A | Certified RC2 product/application source |
| `app/Application/ActiveGenerationBootstrap/DeterministicActiveProjectionGenerationBootstrapV1.php` | Include | A | Certified RC2 product/application source |
| `app/Application/AuthoringDraftResume/AuthoringDraftResumeResult.php` | Include | A | Certified RC2 product/application source |
| `app/Application/AuthoringDraftResume/AuthoringDraftResumeStatus.php` | Include | A | Certified RC2 product/application source |
| `app/Application/AuthoringDraftResume/Contract/AuthoringDraftResumeReaderV1.php` | Include | A | Certified RC2 product/application source |
| `app/Application/AuthoringDraftResume/DeterministicAuthoringDraftResumeReaderV1.php` | Include | A | Certified RC2 product/application source |
| `app/Application/ListingPublicationEventConsumer/ListingPublicationEventDeliveryConsumer.php` | Include | A | Certified RC2 product/application source |
| `app/Application/MediaItemLifecycleEventConsumption/MediaItemLifecycleDeliveryConsumer.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PlaceLifecycleEventConsumption/PlaceLifecycleDeliveryConsumer.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PropertyAuthoringGeographySelection/Contract/GeographySelectionReplayValidatorV1.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PropertyAuthoringGeographySelection/DeterministicGeographySelectionReplayValidatorV1.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PropertyAuthoringGeographySelection/GeographySelectionReplayResult.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PropertyAuthoringGeographySelection/GeographySelectionReplayStatus.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PropertyAuthoringSourceCompleteness/Contract/PropertyAuthoringStateEnricherV1.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PropertyAuthoringSourceCompleteness/DeterministicPropertyAuthoringStateEnricherV1.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PropertyAuthoringSourceCompleteness/PropertyAuthoringEnrichmentResult.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PropertyAuthoringSourceCompleteness/PropertyAuthoringEnrichmentStatus.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PropertyListingAuthoringHttp/DeterministicPropertyListingAuthoringHttpRuntime.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PropertyListingAuthoringOperations/AuthoringOperationCommand.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PropertyListingAuthoringOperations/DeterministicPropertyListingAuthoringOperations.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicAuthoringIntegration/DeterministicPublicAuthoringJourney.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicAuthoringIntegration/PublicAuthoringJourneyRequest.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyMaterialization/CatchUpPublicGeographyDecisionV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyMaterialization/Contract/MaterializePublicGeographyDecisionV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyMaterialization/DeterministicPublicGeographyDecisionMaterializerV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyMaterialization/PublicGeographyDecisionV2Assembler.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyMaterialization/PublicGeographyHierarchy.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyMaterialization/PublicGeographyHierarchyReader.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyMaterialization/PublicGeographyMaterializationResult.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyMaterialization/PublicGeographyMaterializationStatus.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyRefresh/AffectedPublicGeographyTerminalPage.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyRefresh/AffectedPublicGeographyTerminalStatus.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyRefresh/Contract/AffectedPublicGeographyTerminalReaderV1.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyRefresh/PlaceRenamedPublicGeographyConsumer.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyRefresh/PlaceRenamedPublicGeographyPayload.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyRefresh/PublicGeographyMutationRefreshConsumer.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyRefresh/RefreshPublicGeographyTerminalV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographyRefresh/RenamePlaceWithPublicGeographyHandoff.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographySource/Contract/PublicGeographyDecisionWriter.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographySource/PublicGeographyBreadcrumbItemV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographySource/PublicGeographyDecisionStatusV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographySource/PublicGeographyDecisionV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographySource/PublicGeographyReadResult.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicGeographySource/PublicGeographyRevisionVectorItemV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaBinaryDelivery/Contract/PublicMediaBinaryContentReader.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaBinaryDelivery/Contract/PublicMediaBinaryOwnerSourceReader.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaBinaryDelivery/Contract/ResolvePublicMediaBinaryV1.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaBinaryDelivery/DeterministicPublicMediaBinaryResolverV1.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaBinaryDelivery/PublicMediaBinaryContentResult.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaBinaryDelivery/PublicMediaBinaryContentStatus.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaBinaryDelivery/PublicMediaBinaryResult.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaBinaryDelivery/PublicMediaBinarySource.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaBinaryDelivery/PublicMediaBinarySourceResult.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaBinaryDelivery/PublicMediaBinarySourceStatus.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaBinaryDelivery/PublicMediaBinaryStatus.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaMaterialization/Contract/AffectedPublicMediaListingReaderV1.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaMaterialization/Contract/CatchUpPublicMediaDecisionV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaMaterialization/Contract/MaterializePublicMediaDecisionV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaMaterialization/Contract/PublicMediaOwnerSourceReaderV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaMaterialization/DeterministicPublicMediaDecisionMaterializerV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaMaterialization/PublicMediaMaterializationResult.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaMaterialization/PublicMediaMaterializationStatus.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaMaterialization/PublicMediaOwnerItemV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaMaterialization/PublicMediaOwnerSourceResult.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaMaterialization/PublicMediaOwnerSourceStatus.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaMaterialization/PublicMediaOwnerSourceV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaSource/PublicMediaDecision.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaSource/PublicMediaItem.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaSource/PublicMediaItemV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaSource/PublicMediaSourceRevisionRelationV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicMediaSource/PublicMediaSourceRevisionV2.php` | Include | A | Certified RC2 product/application source |
| `app/Application/PublicProjectionUpdater/PublicListingProjectionSources.php` | Include | A | Certified RC2 product/application source |
| `app/Console/Commands/BootstrapActiveProjectionGeneration.php` | Include | A | Certified RC2 product/application source |
| `app/Console/Commands/CatchUpPublicMediaDecision.php` | Include | A | Certified RC2 product/application source |
| `app/Console/Commands/CreateLocalFirstListing.php` | Include | A | Certified RC2 product/application source |
| `app/Console/Commands/CreateLocalPublicFactListing.php` | Include | A | Certified RC2 product/application source |
| `app/Console/Commands/InspectPublicProjectionCandidate.php` | Include | A | Certified RC2 product/application source |
| `app/Console/Commands/MaterializeContentSeoSnapshot.php` | Include | A | Certified RC2 product/application source |
| `app/Console/Commands/MaterializePublicSearchDecision.php` | Include | A | Certified RC2 product/application source |
| `app/Http/Controllers/AuthoringWorkspaceController.php` | Include | A | Certified RC2 product/application source |
| `app/Http/Controllers/MediaAuthoringHttpController.php` | Include | A | Certified RC2 product/application source |
| `app/Http/Controllers/PropertyAuthoringGeographySelectionController.php` | Include | A | Certified RC2 product/application source |
| `app/Http/Controllers/PublicationReviewExperienceController.php` | Include | A | Certified RC2 product/application source |
| `app/Http/Controllers/PublicAuthoringJourneyController.php` | Include | A | Certified RC2 product/application source |
| `app/Http/Controllers/PublicMediaBinaryController.php` | Include | A | Certified RC2 product/application source |
| `app/Http/Requests/PropertyAuthoringGeographySelectionRequest.php` | Include | A | Certified RC2 product/application source |
| `app/Http/Requests/PropertyListingAuthoringHttpRequest.php` | Include | A | Certified RC2 product/application source |
| `app/Http/Requests/PublicationReviewExperienceRequest.php` | Include | A | Certified RC2 product/application source |
| `app/Http/Requests/PublicAuthoringJourneyHttpRequest.php` | Include | A | Certified RC2 product/application source |
| `app/Infrastructure/ActiveGenerationBootstrap/PostgreSqlActiveGenerationBootstrapStateReader.php` | Include | A | Certified RC2 product/application source |
| `app/Infrastructure/ContentSeoSnapshotMaterialization/PostgreSqlContentSeoMaterializationSourceReaderV1.php` | Include | A | Certified RC2 product/application source |
| `app/Infrastructure/ProjectionRuntimeSource/CertifiedPublicListingProjectionSource.php` | Include | A | Certified RC2 product/application source |
| `app/Infrastructure/PublicGeographySource/PostgreSql/PostgreSqlAffectedPublicGeographyTerminalReader.php` | Include | A | Certified RC2 product/application source |
| `app/Infrastructure/PublicGeographySource/PostgreSql/PostgreSqlPublicGeographyMapper.php` | Include | A | Certified RC2 product/application source |
| `app/Infrastructure/PublicGeographySource/PostgreSql/PostgreSqlPublicGeographyReader.php` | Include | A | Certified RC2 product/application source |
| `app/Infrastructure/PublicGeographySource/PostgreSql/PostgreSqlPublicGeographyWriter.php` | Include | A | Certified RC2 product/application source |
| `app/Infrastructure/PublicMediaBinaryDelivery/LaravelFilesystemPublicMediaBinaryContentReader.php` | Include | A | Certified RC2 product/application source |
| `app/Infrastructure/PublicMediaBinaryDelivery/PostgreSqlPublicMediaBinaryOwnerSourceReader.php` | Include | A | Certified RC2 product/application source |
| `app/Infrastructure/PublicMediaMaterialization/PostgreSqlAffectedPublicMediaListingReader.php` | Include | A | Certified RC2 product/application source |
| `app/Infrastructure/PublicMediaMaterialization/PostgreSqlPublicMediaOwnerSourceReaderV2.php` | Include | A | Certified RC2 product/application source |
| `app/Infrastructure/PublicMediaSource/PostgreSql/PostgreSqlPublicMediaMapper.php` | Include | A | Certified RC2 product/application source |
| `app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlAggregateOutboxTransaction.php` | Include | A | Certified RC2 product/application source |
| `app/Infrastructure/SearchDecisionMaterialization/PostgreSqlPublicSearchMaterializationSourceReaderV1.php` | Include | A | Certified RC2 product/application source |
| `app/Projections/SeoListingProjection.php` | Include | A | Certified RC2 product/application source |
| `app/Projections/SeoListingProjectionBuilder.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/ActiveGenerationBootstrapServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/AddressIdentityServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/AuthoringDraftResumeServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/BusinessYearAuthorityServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/ContentSeoSnapshotMaterializationServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/GeographicPlaceCatalogServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/GeographyPlacePersistenceServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/GeographySelectionServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/ListingPublicationCommandGatewayServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/PropertyAuthoringGeographySelectionServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/PropertyAuthoringSourceCompletenessServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/PropertyListingAuthoringOperationsServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/PublicMediaBinaryDeliveryServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/PublicMediaMaterializationServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/PublicProjectionRuntimeServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/PublicPropertyPromotionServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/Providers/PublicSearchDecisionMaterializationServiceProvider.php` | Include | A | Certified RC2 product/application source |
| `app/ReadModels/PublicListingReadModel.php` | Include | A | Certified RC2 product/application source |
| `app/ReadModels/PublicListingReadModelBuilder.php` | Include | A | Certified RC2 product/application source |
| `bootstrap/providers.php` | Include | A | Certified RC2 product/application source |
| `docs/content-seo/public-listing-canonical-path-authority-01/CANONICAL-POLICY-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/CANONICAL-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/COLLISION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/CONTENTSEO-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/CURRENT-RC2-CANONICAL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/GEOGRAPHY-CHANGE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/HISTORICAL-URL-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/HOSTNAME-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/IDENTITY-RELATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/IMMUTABILITY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/LEGACY-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/LISTING-ID-PATH-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/MIGRATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/MULTISITE-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/NORMALIZATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/OWNERSHIP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/PROPERTY-TYPE-CHANGE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/PUBLIC-LISTING-CANONICAL-PATH-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/PUBLIC-ROUTE-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/REPLAY-STABILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/SLUG-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/SLUG-SOURCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/TEST-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/TITLE-CHANGE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/public-listing-canonical-path-authority-01/UNIQUENESS-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/AUTHORITY-COMPLETION-01.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/CANONICAL-INTEGRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/CANONICAL-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/CATCH-UP-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/CONCURRENCY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/CONTENT-SEO-SNAPSHOT-MATERIALIZATION-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/CURRENT-RC2-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/DECISION-TIME-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/FINAL-CATCH-UP-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/FINAL-DECISION-TIME-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/FINAL-FAILURE-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/FINAL-IDENTITY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/FINAL-PUBLISHED-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/FINAL-SNAPSHOT-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/FINAL-VERSION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/IDEMPOTENCY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/IMPLEMENTATION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/IMPLEMENTATION-GATE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/INDEXABILITY-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/MATERIALIZATION-ORDER.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/MIGRATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/PUBLISHED-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/RC2-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/SNAPSHOT-IDENTITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/SNAPSHOT-PAYLOAD.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/SOURCE-REVISION-SET.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/TEST-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/TITLE-DESCRIPTION-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/TRANSACTION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/VERSION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-authority-01/WRITER-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-implementation-01/CANONICAL-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-implementation-01/CATCH-UP-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-implementation-01/CONTENT-SEO-SNAPSHOT-MATERIALIZATION-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-implementation-01/DECISION-TIME-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-implementation-01/IDEMPOTENCY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-implementation-01/POSTGRESQL-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-implementation-01/PROJECTION-SOURCE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-implementation-01/PUBLISHED-HANDOFF-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-implementation-01/SNAPSHOT-IDENTITY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-implementation-01/SOURCE-ASSEMBLY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-implementation-01/VERSION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/content-seo/snapshot-materialization-implementation-01/WRITER-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/AUTHORITY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/BLUEPRINT-CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/DETERMINISM-PAGINATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/GEOGRAPHY-SELECTION-BLUEPRINT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/HIERARCHY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/HTTP-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/IAM-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/PROJECTION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/PROPERTY-AUTHORING-INTEGRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/READER-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/RESULT-CATALOG.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/SELECTABILITY-RULES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/blueprint-01/SELECTION-ITEM.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/implementation-01/GEOGRAPHY-SELECTION-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/implementation-01/IMPLEMENTATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/geography-selection-authority/implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/discovery-blueprint-01/AGGREGATE-PERSISTENCE-MAP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/discovery-blueprint-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/discovery-blueprint-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/discovery-blueprint-01/CURRENT-STORAGE-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/discovery-blueprint-01/HIERARCHY-MERGE-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/discovery-blueprint-01/LOCKING-TRANSACTION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/discovery-blueprint-01/MIGRATION-STRATEGY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/discovery-blueprint-01/PLACE-PERSISTENCE-DISCOVERY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/discovery-blueprint-01/PLACE-REGISTRY-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/discovery-blueprint-01/RELATIONAL-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/discovery-blueprint-01/REPOSITORY-DESIGN.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/discovery-blueprint-01/SELECTION-READ-DEPENDENCY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/implementation-01/IMPLEMENTATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/implementation-01/MIGRATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/implementation-01/PLACE-PERSISTENCE-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/geography/place-persistence-foundation/implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/ACCOUNT-STATE-REQUIREMENTS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/AUTHORING-AUTHORIZATION-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/AUTHORING-HTTP-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/AUTHORING-OWNER-ACCESS-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/CAPABILITY-INVENTORY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/LOCAL-DEMONSTRATION-PRINCIPAL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/PARTICULIER-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/RC2-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/REGISTER-ACCOUNT-INTEGRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/RESOURCE-OWNERSHIP-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/ROLE-INVENTORY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/SECURITY-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/authoring-owner-access-authority-01/SESSION-AUTHORITY-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/local-authoring-principal-provisioning-01/AUTHORING-ACCESS-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/local-authoring-principal-provisioning-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/local-authoring-principal-provisioning-01/LOGIN-SESSION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/local-authoring-principal-provisioning-01/PROVISIONING-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/iam/local-authoring-principal-provisioning-01/SECURITY-CHECK.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/local/postgresql-isolation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/local/postgresql-isolation-01/FAIL-SAFE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/local/postgresql-isolation-01/ISOLATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/local/postgresql-isolation-01/NON-DESTRUCTION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/local/postgresql-isolation-01/POSTGRESQL-CONFIGURATION-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/local/postgresql-isolation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/local/RC2-CONTROLLED-BROWSER-LOCAL-HTTPS-ACCESS-QUALIFICATION-01.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/ACTIVATE-CONTRACT-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/ACTIVE-GENERATION-BOOTSTRAP-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/ACTIVE-UNIQUENESS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/AUTHORIZED-ACTOR.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/AUTOMATIC-STARTUP-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/BOOTSTRAP-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/COMMAND-IDENTITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/CONCURRENCY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/CREATE-CONTRACT-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/DEPLOYMENT-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/EMPTY-GENERATION-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/ENVIRONMENT-SCOPE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/GENERATION-ID-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/GENERATION-IDENTITY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/IDEMPOTENCY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/IMPLEMENTATION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/INITIAL-BOOTSTRAP-ORDER.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/INITIAL-VS-REBUILD-GENERATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/LOCAL-RC2-BOOTSTRAP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/MIGRATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/OPERATOR-COMMAND.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/PROJECTION-SEQUENCING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/RC2-GENERATION-IDENTITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/RC2-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/REBUILDER-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/RELEASE-IDENTITY-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/SECURITY-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/TEST-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/TRANSACTION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-authority-01/VALIDATOR-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/ACTIVATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/ACTIVATION-EVIDENCE-02.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/ACTIVE-GENERATION-BOOTSTRAP-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/BOOTSTRAP-CONTRACT-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/CANDIDATE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/CANDIDATE-EVIDENCE-02.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/CERTIFICATION-NOTE-02.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/CONCURRENCY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/GENERATION-IDENTITY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/IDEMPOTENCY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/IDEMPOTENCY-EVIDENCE-02.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/MANIFEST-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/MANIFEST-EVIDENCE-02.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/OPERATOR-COMMAND-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/OPERATOR-COMMAND-EVIDENCE-02.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/POST-BOOTSTRAP-SOURCE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/POSTGRESQL-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/POSTGRESQL-EVIDENCE-02.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/PROJECTION-SOURCE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/RC2-BOOTSTRAP-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/READINESS-CLOSURE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/REBUILD-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/REBUILD-EVIDENCE-02.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/REOPENING-COMPLETION-01.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/VALIDATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/VALIDATION-EVIDENCE-02.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-bootstrap-implementation-01/VALIDATION-REPORT-02.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/ACTIVE-GENERATION-MISSING-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/ACTIVE-GENERATION-SOURCE-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/ACTIVE-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/BOOTSTRAP-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/CONCURRENCY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/CORRECTION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/CURRENT-APPLICATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/FAILURE-CLASSIFICATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/GENERATION-CANDIDATE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/GENERATION-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/HISTORICAL-AUTHORITY-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/IDEMPOTENCY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/IDENTITY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/LIFECYCLE-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/MIGRATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/NORMAL-PATH.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/OWNERSHIP-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/PROJECTION-SEQUENCING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/RC2-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/RC2-READINESS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/READER-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/REBUILD-RELATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/RELEASE-DEPLOYMENT-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/STORE-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/TRANSACTION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/active-generation-source-authority-01/WRITER-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/ACTIVE-GENERATION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/CANONICAL-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/CATCH-UP-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/CONTENT-SEO-MISSING-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/CONTENT-SEO-SOURCE-DEPENDENCY-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/CORRECTION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/CURRENT-RC2-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/CURRENT-RC2-FACT-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/DATA-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/DECISION-TIME-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/DEPENDENCY-GRAPH.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/FAILURE-CLASSIFICATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/IDENTITY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/INDEXABILITY-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/NORMAL-MATERIALIZATION-ORDER.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/OWNERSHIP-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/PROJECTION-SEQUENCING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/PUBLISHED-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/RC2-CATCH-UP-READINESS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/RC2-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/READER-BINDING-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/SEARCH-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/SNAPSHOT-ORIGIN.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/SOURCE-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/STORE-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/TITLE-DESCRIPTION-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/UI-NOTREADY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/VERSION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/content-seo-source-dependency-authority-01/WRITER-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/CURRENT-RC2-GEOGRAPHY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/CURRENT-RC2-MEDIA.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/DEPENDENCY-GRAPH.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/FAILURE-CLASSIFICATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/GEOGRAPHY-CATCH-UP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/GEOGRAPHY-DATA-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/GEOGRAPHY-IDENTITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/GEOGRAPHY-MATERIALIZATION-PATH.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/GEOGRAPHY-SOURCE-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/GEOGRAPHY-VERSION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/MEDIA-CATCH-UP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/MEDIA-DATA-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/MEDIA-IDENTITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/MEDIA-MATERIALIZATION-PATH.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/MEDIA-SOURCE-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/MEDIA-VERSION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/MIGRATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/NEXT-GATE-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/OWNERSHIP-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/PUBLIC-GEOGRAPHY-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/PUBLIC-GEOGRAPHY-MEDIA-SOURCE-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/PUBLIC-MEDIA-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/RC2-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/READINESS-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/READINESS-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/STORE-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/TRANSACTION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/public-geography-media-source-authority-01/WRITER-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/CERTIFIED-SOURCE-ASSEMBLY-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/CORRECTION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/CURRENT-RC2-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/DATA-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/DEPENDENCY-GRAPH.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/FAILURE-CLASSIFICATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/HISTORICAL-AUTHORITY-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/MATERIALIZATION-PATH.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/OWNERSHIP-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/RC2-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/SEARCH-CLOSED-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/SEARCH-MISSING-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/SEARCH-SOURCE-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/SEARCH-SOURCE-DEPENDENCY-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/projection/search-source-dependency-authority-01/UI-NOTREADY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/ADDRESS-CREATION-INTEGRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/ADDRESS-IDENTITY-BLUEPRINT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/AUTHORITY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/BLUEPRINT-CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/CHANGE-ADDRESS-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/COLLISION-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/COMMAND-IDENTITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/IDENTITY-NATURE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/IDENTITY-STRATEGY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/ISSUANCE-TIMING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/ISSUER-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/REPLAY-IDEMPOTENCY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/RESULT-CATALOG.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/blueprint-01/TRANSACTION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/implementation-01/ADDRESS-IDENTITY-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/implementation-01/IMPLEMENTATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/address-identity-authority/implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/AUTHORING-DRAFT-RESUME-BLUEPRINT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/CONSISTENCY-INVARIANTS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/EXISTING-CONTRACT-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/GEOGRAPHY-REHYDRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/HTTP-ENTRY-POINT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/IMPLEMENTATION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/MEDIA-REHYDRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/MULTIPLE-DRAFT-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/OWNER-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/PORTFOLIO-INTEGRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/PRODUCT-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/RC2-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/RESUME-IDENTITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/RESUME-READ-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/SECURITY-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/SESSION-EXPIRY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/STATE-REHYDRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/STEP-RESTORATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/blueprint-01/TEST-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/implementation-01/AUTHORING-DRAFT-RESUME-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/implementation-01/GEOGRAPHY-REHYDRATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/implementation-01/HTTP-RESUME-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/implementation-01/MEDIA-REHYDRATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/implementation-01/RC2-DRAFT-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/implementation-01/RESUME-READER-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/implementation-01/STATE-REHYDRATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/authoring-draft-resume-authority/implementation-01/ZERO-MUTATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/AUTHORITATIVE-INSTANT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/AUTHORITY-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/AUTHORITY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/BLUEPRINT-CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/BUSINESS-YEAR-BLUEPRINT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/CALENDAR-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/COMMAND-IDENTITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/CONSTRUCTION-YEAR-INTEGRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/OTHER-COMMANDS-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/PROMOTION-INTEGRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/REPLAY-IDEMPOTENCY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/RESULT-CATALOG.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/SOURCE-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/TIMEZONE-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/TRANSACTION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/blueprint-01/YEAR-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/implementation-01/BUSINESS-YEAR-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/implementation-01/IMPLEMENTATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/business-year-authority/implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-addressability-authority-01/ADDRESS-GRANULARITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-addressability-authority-01/CATALOG-INTEGRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-addressability-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-addressability-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-addressability-authority-01/EVOLUTION-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-addressability-authority-01/F1-WORKSPACE-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-addressability-authority-01/GEOGRAPHIC-PLACE-ADDRESSABILITY-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-addressability-authority-01/NOT-ADDRESSABLE-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-addressability-authority-01/OWNERSHIP-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-addressability-authority-01/PLACE-TYPE-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-addressability-authority-01/POLICY-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-addressability-authority-01/PROPERTY-TYPE-INDEPENDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-addressability-authority-01/REGISTER-CHANGEADDRESS-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/ADAPTER-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/ADDRESSABILITY-INTEGRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/BINDING-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/BLUEPRINT-COMPLETION-01.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/CONTRACT-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/F5-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/FINAL-STATUS-MAPPING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/GEOGRAPHIC-PLACE-CATALOG-BLUEPRINT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/IMPLEMENTATION-GATE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/REGISTER-PROPERTY-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/SOURCE-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/STATUS-MAPPING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/blueprint-01/TOCTOU-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/implementation-01/GEOGRAPHIC-PLACE-CATALOG-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/implementation-01/IMPLEMENTATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/geographic-place-catalog-authority/implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-geography-selection-experience/authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-geography-selection-experience/authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-geography-selection-experience/authority-01/F4-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-geography-selection-experience/authority-01/GEOGRAPHY-SELECTION-EXPERIENCE-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-geography-selection-experience/authority-01/HTTP-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-geography-selection-experience/authority-01/HTTP-RESULT-MAPPING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-geography-selection-experience/authority-01/LEGACY-LOCATION-FIELDS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-geography-selection-experience/authority-01/SECURITY-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-geography-selection-experience/authority-01/SERVER-VALIDATION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-geography-selection-experience/authority-01/WORKSPACE-SELECTION-FLOW.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-geography-selection-experience/implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-geography-selection-experience/implementation-01/GEOGRAPHY-SELECTION-EXPERIENCE-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-geography-selection-experience/implementation-01/IMPLEMENTATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-geography-selection-experience/implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-source-completeness/implementation-01/AUTHORING-COMPLETENESS-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-source-completeness/implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-source-completeness/implementation-01/IMPLEMENTATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-source-completeness/implementation-01/MIGRATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-authoring-source-completeness/implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities/implementation-sequencing-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities/implementation-sequencing-authority-01/DEPENDENCY-GRAPH.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities/implementation-sequencing-authority-01/FOUNDATION-GATES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities/implementation-sequencing-authority-01/GOVERNANCE-STATUS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities/implementation-sequencing-authority-01/IMPLEMENTATION-SEQUENCING-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities-qualification-01/ADDRESS-IDENTITY-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities-qualification-01/ADDRESS-IDENTITY-VERDICT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities-qualification-01/BUSINESS-YEAR-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities-qualification-01/BUSINESS-YEAR-VERDICT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities-qualification-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities-qualification-01/CROSS-AUTHORITY-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities-qualification-01/GEOGRAPHY-SELECTION-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities-qualification-01/GEOGRAPHY-SELECTION-VERDICT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities-qualification-01/MINIMAL-AUTHORITY-GAPS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/property-supporting-authorities-qualification-01/SUPPORTING-AUTHORITIES-QUALIFICATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-authority-01/AGGREGATE-WORKFLOW-ATOMICITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-authority-01/COMMIT-ROLLBACK-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-authority-01/CURRENT-TRANSACTION-MAP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-authority-01/EXTERNAL-SIDE-EFFECTS-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-authority-01/F7-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-authority-01/IMPLEMENTATION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-authority-01/POSTGRESQL-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-authority-01/PROMOTION-SEPARATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-authority-01/RETRY-IDEMPOTENCY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-authority-01/ROLLBACK-SIGNAL-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-authority-01/TRANSACTIONAL-OUTCOME-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-authority-01/WORKFLOW-OUTCOME-INVENTORY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-implementation-01/RETRY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-implementation-01/ROLLBACK-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-implementation-01/TRANSACTIONAL-OUTCOME-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-a-listing-submit-transactional-outcome-implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/ADDRESS-COMPATIBILITY-CASES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/ADDRESS-EQUALITY-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/ADDRESS-EQUALS-BOUNDARY-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/ADDRESS-ID-NATURE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/ALREADY-APPLIED-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/CANONICAL-COMPATIBILITY-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/CANONICAL-IDENTITY-COMPATIBILITY-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/CURRENT-COMPATIBILITY-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/DIVERGENT-COMMAND-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/F7-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/IMPLEMENTATION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/LEDGER-EXISTING-PROPERTY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/LEGACY-PROPERTY-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-authority-01/SUBMIT-PRECONDITION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-implementation-01/ADDRESS-ID-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-implementation-01/CANONICAL-COMPATIBILITY-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-implementation-01/EXISTING-PROPERTY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-implementation-01/SUBMIT-PRECONDITION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/f7-b-existing-property-canonical-identity-compatibility-implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/implementation-01/COMMAND-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/implementation-01/IMPLEMENTATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/implementation-01/MIGRATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/implementation-01/PUBLIC-PROPERTY-PROMOTION-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/implementation-01/SUBMIT-INTEGRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/implementation-01/TRANSACTION-IDEMPOTENCY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-01/FAILURE-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-01/GEOGRAPHY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-01/IDEMPOTENCY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-01/MIGRATION-100-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-01/PROMOTION-RECERTIFICATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-01/SOURCE-TO-AGGREGATE-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-01/SUBMIT-INTEGRATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-01/TRANSACTION-ATOMICITY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-02/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-02/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-02/FAILURE-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-02/GEOGRAPHY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-02/HISTORICAL-BLOCKER-CLOSURE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-02/IDEMPOTENCY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-02/MIGRATION-100-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-02/PROMOTION-RECERTIFICATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-02/PROPERTY-EXISTENCE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-02/ROLLBACK-RETRY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-02/SOURCE-TO-AGGREGATE-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-02/SUBMIT-INTEGRATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-02/TRANSACTION-ATOMICITY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-02/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/ADDRESS-CANONICALITY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/FAILURE-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/GEOGRAPHY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/HISTORICAL-BLOCKERS-CLOSURE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/IDEMPOTENCY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/MIGRATION-100-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/PROMOTION-RECERTIFICATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/PROPERTY-EXISTENCE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/ROLLBACK-RETRY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/SOURCE-TO-AGGREGATE-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/SUBMIT-INTEGRATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/TRANSACTION-ATOMICITY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion/recertification-03/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/AUTHORING-DOMAIN-MAPPING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/AUTHORITY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/BLUEPRINT-CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/COMMAND-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/EVENT-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/IDEMPOTENCY-REPLAY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/MISSING-DATA-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/PROJECTION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/PROMOTION-TIMING-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/PUBLICATION-INTEGRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/PUBLIC-PROPERTY-PROMOTION-BLUEPRINT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/RESULT-CATALOG.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/blueprint-01/TRANSACTION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/CURRENT-HANDOFF-MAP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/CURRENT-PROPERTY-LIFECYCLE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/DISCOVERY-CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/GAP-ANALYSIS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/OWNERSHIP-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/PROJECTION-PROPERTY-DEPENDENCY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/PROPERTY-EVENT-INVENTORY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/PROPERTY-FOUNDATION-DISCOVERY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/PROPERTY-MODEL-INVENTORY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-promotion-authority-01/PROPERTY-TRANSACTION-BOUNDARIES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/AUTHORING-COMPLETENESS-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/AUTHORING-SCHEMA-TARGET.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/BUSINESS-YEAR-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/COMPLETENESS-RULE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/GEOGRAPHY-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/OWNERSHIP-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/PHYSICAL-FACTS-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-01/ADDRESS-IDENTITY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-01/AUTHORING-COMPLETENESS-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-01/BUSINESS-YEAR-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-01/EXECUTABLE-SOURCE-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-01/GEOGRAPHY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-01/PROMOTION-READINESS-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-01/REGISTER-PROPERTY-REQUIREMENTS-RECHECK.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-01/SOURCE-ASSEMBLY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-01/SOURCE-COMPLETENESS-RECERTIFICATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-02/ADDRESS-IDENTITY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-02/AUTHORING-COMPLETENESS-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-02/BUSINESS-YEAR-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-02/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-02/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-02/EXECUTABLE-SOURCE-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-02/GEOGRAPHY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-02/NEGATIVE-GEOGRAPHY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-02/PROMOTION-READINESS-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-02/REGISTER-PROPERTY-REQUIREMENTS-RECHECK.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-02/SOURCE-ASSEMBLY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-02/SOURCE-COMPLETENESS-RECERTIFICATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/recertification-02/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/REFERENCE-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/REGISTER-PROPERTY-REQUIREMENTS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/SOURCE-COMPLETENESS-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/property/public-property-source-completeness-authority-01/STRUCTURED-ADDRESS-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/ANCESTOR-MUTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/BREADCRUMB-ORDER.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/BREADCRUMB-PAYLOAD.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/CANONICAL-URL-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/CONSUMER-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/CURRENT-RC2-BREADCRUMB.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/CURRENT-RC2-REPRESENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/CURRENT-RC2-REVISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/DETERMINISM.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/HIERARCHY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/LISTING-PROPERTY-RELATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/LOCALIZATION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/MATERIALIZATION-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/MIGRATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/MISSING-LEVELS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/NORMALIZATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/OWNERSHIP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/PARENT-MUTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/PLACE-IDENTITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/PLACE-TYPE-CATALOG.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/PUBLIC-AVAILABILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/PUBLIC-DECISION-SCOPE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/PUBLIC-GEOGRAPHY-CANONICAL-PLACE-REPRESENTATION-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/PUBLIC-LABEL-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/REPRESENTATION-PAYLOAD.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/RETIRED-PLACE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/REVISION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/SLUG-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/SOURCE-REVISION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/canonical-place-representation-authority-01/TEST-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/AUTHORITY-COMPLETION-01.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/AUTHORITY-COMPLETION-02.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/AUTHORITY-COMPLETION-03.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/CANONICAL-REPRESENTATION-INTEGRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/CATCH-UP-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/CONCURRENCY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/CURRENT-RC2-FACTS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/DECISION-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/DTO-URL-ALIGNMENT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-AFFECTED-TERMINAL-READER.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-AVAILABILITY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-CATCH-UP-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-CONSUMER-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-FANOUT-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-IDENTITY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-IMPLEMENTATION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-IMPLEMENTATION-BOUNDARY-03.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-INITIAL-MATERIALIZATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-MUTATION-TRANSPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-PRODUCTIVE-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-RC2-CATCH-UP-READINESS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-RC2-READINESS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-REFRESH-MATERIALIZATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-REFRESH-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-REPRESENTATION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-REVISION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-SOURCE-READER.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-STORE-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-TRIGGER-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-V2-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/FINAL-VERSION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/IDEMPOTENCY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/IDENTITY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/IMPLEMENTATION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/IMPLEMENTATION-GATE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/IMPLEMENTATION-GATE-02.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/IMPLEMENTATION-GATE-03.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/INPUT-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/MATERIALIZER-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/MIGRATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/NORMAL-PATH.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/OWNERSHIP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/PAYLOAD-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/PUBLIC-GEOGRAPHY-DECISION-MATERIALIZATION-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/RC2-CATCH-UP-READINESS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/READINESS-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/TEST-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/TRANSACTION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/TRIGGER-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/VERSION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-authority-01/WRITER-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/AFFECTED-TERMINAL-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/CATCH-UP-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/CONSUMER-REGRESSION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/INITIAL-HANDOFF-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/MUTATION-TRANSPORT-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/PAGINATION-REPLAY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/POSTGRESQL-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/PUBLIC-GEOGRAPHY-DECISION-MATERIALIZATION-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/RC2-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/REFRESH-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/REVISION-VECTOR-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/SOURCE-READER-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/V2-REPRESENTATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/decision-materialization-implementation-01/WRITER-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01.zip` | Exclude | G | Generated duplicate archive; normative directory included |
| `docs/public-geography/descendant-decision-refresh-authority-01/AFFECTED-TERMINAL-DATA-SOURCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/AFFECTED-TERMINAL-READER.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/AFFECTED-TERMINAL-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/CONCURRENCY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/CURRENT-RC2-IMPACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/DECISION-BASED-LOOKUP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/DISABLE-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/DOMAIN-EVENT-INVENTORY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/ENABLE-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/EVENT-IDENTITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/FANOUT-IDENTITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/HIERARCHY-BASED-LOOKUP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/IMPLEMENTATION-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/INITIAL-VS-REFRESH.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/MERGE-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/MIGRATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/MISSING-DECISION-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/MUTATION-CATALOG.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/ORDERING-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/PAGINATION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/PLACE-RENAMED-TRANSPORT-GAP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/PROGRESS-RECOVERY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/PUBLIC-DECISION-SCOPE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/PUBLIC-GEOGRAPHY-DESCENDANT-DECISION-REFRESH-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/QUARANTINE-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/REFRESH-MATERIALIZER-ENTRY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/RENAME-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/REPLAY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/RETIRED-PLACE-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/RETRY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/SOURCE-SEQUENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/TERMINAL-DISCOVERY-SCOPE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/TEST-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/TRANSACTION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/descendant-decision-refresh-authority-01/TRANSPORT-CATALOG.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/ACCESSIBILITY-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/ARCHITECTURE-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/BLADE-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/CHECKSUM-IDEMPOTENCY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/CONSUMER-INVENTORY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/CONTENTSEO-AUTHORITY-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/CONTENTSEO-BREADCRUMB-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/CONTENTSEO-REGRESSION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/CONTENTSEO-V2-ADAPTER.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/CONTRACT-VERSIONING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/CURRENT-RC2-IMPACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/DATA-MIGRATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/GENERATION-REBUILD-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/IMPLEMENTATION-SCOPE-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/IMPLEMENTATION-SEQUENCING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/LISTING-CANONICAL-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/MATERIALIZATION-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/MIGRATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/NO-FAKE-URL-INVARIANT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/PROJECTION-DTO-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/PROJECTION-OUTPUT-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/PROJECTION-SOURCE-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/PROJECTION-SOURCE-V2-ADAPTER.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/PUBLIC-GEOGRAPHY-V2-CONSUMER-BREADCRUMB-ALIGNMENT-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/PUBLIC-PAGE-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/READ-MODEL-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/STRUCTURED-DATA-SEO-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/TEST-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/UX-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-authority-01/V1-V2-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-implementation-01/ARCHITECTURE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-implementation-01/CHECKSUM-SERIALIZATION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-implementation-01/CONTENTSEO-ADAPTER-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-implementation-01/NO-FAKE-URL-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-implementation-01/PROJECTION-DTO-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-implementation-01/PROJECTION-SOURCE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-implementation-01/PUBLIC-GEOGRAPHY-V2-CONSUMER-BREADCRUMB-ALIGNMENT-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-implementation-01/PUBLIC-PAGE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-implementation-01/READ-MODEL-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-implementation-01/V1-V2-COMPATIBILITY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-geography/v2-consumer-breadcrumb-alignment-implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-implementation-01/HTTP-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-implementation-01/NO-STORAGEKEY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-implementation-01/OWNER-ELIGIBILITY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-implementation-01/PUBLIC-MEDIA-BINARY-DELIVERY-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-implementation-01/PUBLICMEDIAITEM-V2-COMPATIBILITY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-implementation-01/RC2-DELIVERY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-implementation-01/REVISION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-implementation-01/ROUTE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-implementation-01/SECURITY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-implementation-01/STORAGE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/AUTHORIZATION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/BINARY-ACCESS-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/CACHE-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/CDN-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/CONTRACT-VERSIONING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/CURRENT-RC2-DELIVERY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/DELIVERY-DECISION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/DELIVERY-REVISION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/DIRECT-STORAGE-URL-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/ENVIRONMENT-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/EXISTING-DELIVERY-PATHS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/HOSTNAME-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/MATERIALIZATION-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/MIGRATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/MIME-CONTENT-TYPE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/NO-CYCLE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/OWNERSHIP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/PERSISTED-VS-DERIVED-URL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/PUBLIC-MEDIA-BINARY-DELIVERY-URL-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/PUBLIC-MEDIA-IDENTITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/PUBLIC-MEDIA-ITEM-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/PUBLIC-URL-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/RC2-READINESS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/REVOCATION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/SECURITY-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/SIGNED-URL-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/STABILITY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/STABLE-ROUTE-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/STORAGE-BACKEND-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/STORAGE-KEY-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/TEST-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/UNAVAILABLE-DELETED-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/URL-INVALIDATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/URL-SHAPE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/binary-delivery-url-authority-01/VARIANT-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/AUTHORITY-COMPLETION-01.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/BINARY-DELIVERY-INTEGRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/CATCH-UP-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/CONCURRENCY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/CURRENT-RC2-MEDIA-FACTS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/DECISION-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/EMPTY-MEDIA-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/EVENT-TRANSPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/FINAL-DELIVERY-LOCATOR.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/FINAL-MUTATION-REFRESH.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/FINAL-PUBLIC-MEDIA-ITEM-V2.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/FINAL-RC2-CATCH-UP-READINESS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/FINAL-SOURCE-REVISION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/FINAL-VERSION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/IDEMPOTENCY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/IDENTITY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/IMPLEMENTATION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/IMPLEMENTATION-GATE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/IMPLEMENTATION-SEQUENCING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/INITIAL-TRIGGER.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/INITIAL-VS-REFRESH.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/MATERIALIZER-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/MEDIA-VARIANTS-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/MIGRATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/MUTATION-CATALOG.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/ORDER-CHANGE-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/ORDERING-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/OWNERSHIP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/PAYLOAD-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/PRIMARY-MEDIA-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/PUBLICATION-ELIGIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/PUBLIC-MEDIA-DECISION-MATERIALIZATION-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/PUBLIC-MEDIA-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/RC2-CATCH-UP-READINESS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/READINESS-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/SOURCE-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/SOURCE-REVISION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/STORE-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/TEST-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/TRANSACTION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/UNAVAILABLE-MEDIA-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/URL-BINARY-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/VERSION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-authority-01/WRITER-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/BINARY-DELIVERY-REGRESSION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/CANDIDATE-READINESS-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/CATCH-UP-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/IDEMPOTENCY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/INITIAL-HANDOFF-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/LOCATOR-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/MUTATION-REFRESH-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/POSTGRESQL-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/PUBLIC-MEDIA-DECISION-MATERIALIZATION-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/PUBLICMEDIAITEM-V2-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/RC2-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/SOURCE-READER-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/SOURCE-REVISION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/public-media/decision-materialization-implementation-01/WRITER-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/release/CERTIFICATION-NOTE.md` | Include | D | Release engineering source/evidence |
| `docs/release/ITERATION-01.md` | Include | D | Release engineering source/evidence |
| `docs/release/ITERATION-02.md` | Include | D | Release engineering source/evidence |
| `docs/release/ITERATION-03.md` | Include | D | Release engineering source/evidence |
| `docs/release/ITERATION-04.md` | Include | D | Release engineering source/evidence |
| `docs/release/ITERATION-05.md` | Include | D | Release engineering source/evidence |
| `docs/release/ITERATION-06.md` | Include | D | Release engineering source/evidence |
| `docs/release/ITERATION-07.md` | Include | D | Release engineering source/evidence |
| `docs/release/ITERATION-08.md` | Include | D | Release engineering source/evidence |
| `docs/release/ITERATION-09.md` | Include | D | Release engineering source/evidence |
| `docs/release/ITERATION-10.md` | Include | D | Release engineering source/evidence |
| `docs/release/ITERATION-11.md` | Include | D | Release engineering source/evidence |
| `docs/release/publication-review-notready-result-reduction-correction-01/ARCHITECTURE-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/publication-review-notready-result-reduction-correction-01/CERTIFICATION-NOTE.md` | Include | D | Release engineering source/evidence |
| `docs/release/publication-review-notready-result-reduction-correction-01/CURRENT-REDUCTION-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/publication-review-notready-result-reduction-correction-01/HTTP-CONTRACT-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/publication-review-notready-result-reduction-correction-01/NOTREADY-REGRESSION-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/publication-review-notready-result-reduction-correction-01/NOTREADY-RESULT-REDUCTION-CORRECTION.md` | Include | D | Release engineering source/evidence |
| `docs/release/publication-review-notready-result-reduction-correction-01/ROOT-CAUSE.md` | Include | D | Release engineering source/evidence |
| `docs/release/publication-review-notready-result-reduction-correction-01/SUCCESS-REGRESSION-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/publication-review-notready-result-reduction-correction-01/UI-REDUCTION-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/publication-review-notready-result-reduction-correction-01/VALIDATION-REPORT.md` | Include | D | Release engineering source/evidence |
| `docs/release/RC2-CERTIFICATION-NOTE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-immutable-source-materialization-01/PREFLIGHT-GIT-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-immutable-source-materialization-01/PRE-STAGING-VALIDATION.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-immutable-source-materialization-01/R5-DIVERGENCE-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-immutable-source-materialization-01/RC2-IMMUTABLE-SOURCE-MATERIALIZATION.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-immutable-source-materialization-01/SECRET-AUDIT.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-immutable-source-materialization-01/STAGED-DIFF-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-immutable-source-materialization-01/STAGED-SECRET-AUDIT.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-immutable-source-materialization-01/STAGING-MANIFEST.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-immutable-source-materialization-01/STAGING-MANIFEST-REVISED.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-immutable-source-materialization-01/TEMPORARY-FILE-AUDIT.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-immutable-source-materialization-01/WORKTREE-INVENTORY.md` | Include | D | Release engineering source/evidence |
| `docs/release/RC2-IMPLEMENTATION-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/ACTIVE-GENERATION-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/AUTHORING-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/CERTIFICATION-NOTE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/DATA-INTEGRITY-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/EVIDENCE-MATRIX.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/EXIT-CRITERIA.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/FINAL-END-TO-END-CERTIFICATION.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/HISTORICAL-DIVERGENCE-REGISTER.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/IDEMPOTENCY-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/PROJECTION-COHERENCE-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/PUBLICATION-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/PUBLIC-RUNTIME-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/RC2-IDENTITY-REGISTER.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/RESIDUAL-RISK-REGISTER.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/SOURCE-READINESS-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-iteration-11-final-certification-01/UI-NOTREADY-RESIDUAL-AUDIT.md` | Include | D | Release engineering source/evidence |
| `docs/release/RC2-ITERATION-11-REOPENING-02-MANIFEST.json` | Include | D | Release engineering source/evidence |
| `docs/release/RC2-ITERATION-11-REOPENING-03-MANIFEST.json` | Include | D | Release engineering source/evidence |
| `docs/release/RC2-ITERATION-11-REOPENING-04-MANIFEST.json` | Include | D | Release engineering source/evidence |
| `docs/release/RC2-MEDIA-503-CORRECTION.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-active-generation-runtime-readiness-audit-01/ACTIVE-GENERATION-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-active-generation-runtime-readiness-audit-01/CERTIFICATION-NOTE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-active-generation-runtime-readiness-audit-01/CONTENTSEO-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-active-generation-runtime-readiness-audit-01/FIRST-DIVERGENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-active-generation-runtime-readiness-audit-01/GEOGRAPHY-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-active-generation-runtime-readiness-audit-01/MEDIA-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-active-generation-runtime-readiness-audit-01/PROJECTION-RECORD-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-active-generation-runtime-readiness-audit-01/PUBLIC-PAGE-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-active-generation-runtime-readiness-audit-01/PUBLIC-READ-MODEL-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-active-generation-runtime-readiness-audit-01/RUNTIME-READINESS-AUDIT.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-active-generation-runtime-readiness-audit-01/SEARCH-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-active-generation-runtime-readiness-audit-01/SOURCE-PROJECTION-COHERENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/CERTIFICATION-NOTE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/CI-REPRODUCIBILITY-STATUS.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/COMMIT-TAG-AUTHORITY.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/COMPATIBILITY-REPORT.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/DEPENDENCY-RESTORE-STATUS.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/EVIDENCE-COMPLETENESS.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/IMMUTABILITY-REQUIREMENT.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/ITERATION-11-CLOSURE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/ITERATION-12-AUDIT.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/LOCAL-VS-RELEASE-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/NEXT-AUTHORIZED-GATE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/NEXT-GATE-RESOLUTION.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/NORMATIVE-REGISTER-INVENTORY.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/PACKAGING-STATUS.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/PHASE-5.9-RELATION.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/PRODUCTION-READINESS-STATUS.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/RC2-POST-ITERATION-11-SEQUENCING-AUTHORITY.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/RC2-STATUS.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/RESIDUAL-UI-DEFECT-SEQUENCING.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/SEARCH-UX-API-BOUNDARY.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/SEQUENCING-MATRIX.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-iteration-11-sequencing-authority-01/WORKTREE-MATERIALIZATION.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/CERTIFICATION-NOTE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/COMPATIBILITY-REPORT.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/DEPENDENCY-RESTORE-STATUS.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/EXTERNAL-CI-STATUS.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/IMMUTABLE-MATERIALIZATION-REQUIREMENT.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/ITERATION-12-STATUS.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/NEXT-AUTHORIZED-GATE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/NEXT-GATE-RESOLUTION.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/PACKAGING-STATUS.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/PHASE-5.9-RELATION.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/PREVIOUS-SEQUENCING-CLOSURE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/PRODUCTION-READINESS-STATUS.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/R5-OBSOLESCENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/RC2-CURRENT-STATUS.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/RC2-POST-NOTREADY-CORRECTION-SEQUENCING-AUTHORITY.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/SEARCH-UX-API-STATUS.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/UI-DEFECT-CLOSURE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-post-notready-correction-sequencing-authority-01/WORKTREE-STATUS.md` | Include | D | Release engineering source/evidence |
| `docs/release/RC2-STABILIZATION.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-staged-snapshot-whitespace-normalization-correction-01/CERTIFICATION-NOTE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-staged-snapshot-whitespace-normalization-correction-01/EOF-NORMALIZATION-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-staged-snapshot-whitespace-normalization-correction-01/FAILURE-SET.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-staged-snapshot-whitespace-normalization-correction-01/LINE-ENDING-SAFETY.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-staged-snapshot-whitespace-normalization-correction-01/SECRET-RECHECK.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-staged-snapshot-whitespace-normalization-correction-01/SEMANTIC-NON-CHANGE-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-staged-snapshot-whitespace-normalization-correction-01/STAGING-INVARIANT.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-staged-snapshot-whitespace-normalization-correction-01/TRAILING-WHITESPACE-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-staged-snapshot-whitespace-normalization-correction-01/VALIDATION-REPORT.md` | Include | D | Release engineering source/evidence |
| `docs/release/rc2-staged-snapshot-whitespace-normalization-correction-01/WHITESPACE-NORMALIZATION-CORRECTION.md` | Include | D | Release engineering source/evidence |
| `docs/release/RC2-VALIDATION-REPORT.md` | Include | D | Release engineering source/evidence |
| `docs/release/STABILIZATION-LOG.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/CERTIFICATION-NOTE.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/CHROME-QUALIFICATION.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/CLEAN-PROFILE-REQUIREMENTS.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/COMPATIBILITY-REPORT.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/CREDENTIAL-REENTRY-DECISION.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/EVIDENCE-MANIFEST.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/HTTP-EVIDENCE-MODEL.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/IAM-LOGIN-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/OWNER-REVIEWER-SEPARATION.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/POSTGRESQL-CORROBORATION.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/PROJECTION-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/RC2-EVIDENCE-REQUIREMENTS.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/RC2-HANDOFF.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/REPLAY-EVIDENCE.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/SECURITY-BOUNDARY.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/SYSTEM-BROWSER-EVIDENCE-AUTHORITY.md` | Include | D | Release engineering source/evidence |
| `docs/release/system-browser-real-journey-evidence-authority-01/UI-EVIDENCE-MODEL.md` | Include | D | Release engineering source/evidence |
| `docs/search/public-search-decision-materialization-authority-01/AUTHORITY-COMPLETION-01.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/CATCH-UP-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/CONCURRENCY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/CURRENT-RC2-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/DECISION-IDENTITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/FACET-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/FINAL-CATCH-UP-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/FINAL-DECISION-IDENTITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/FINAL-FAILURE-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/FINAL-MATERIALIZATION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/FINAL-PUBLISHED-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/FINAL-RESULT-CATALOG.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/FINAL-VERSION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/IDEMPOTENCY-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/IMPLEMENTATION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/IMPLEMENTATION-GATE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/INPUT-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/MIGRATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/PROJECTION-SEQUENCING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/PROJECTION-STATE-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/PUBLIC-SEARCH-DECISION-MATERIALIZATION-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/PUBLISHED-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/RANKING-POLICY-INTEGRATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/RC2-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/RESULT-CATALOG.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/SEARCH-RANK-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/SOURCE-REVISION-SET.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/TEST-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/TRANSACTION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/VERSION-MODEL.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-authority-01/WRITER-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-implementation-01/CATCH-UP-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-implementation-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-implementation-01/DECISION-IDENTITY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-implementation-01/IDEMPOTENCY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-implementation-01/POSTGRESQL-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-implementation-01/PROJECTION-SOURCE-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-implementation-01/PUBLIC-SEARCH-DECISION-MATERIALIZATION-IMPLEMENTATION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-implementation-01/PUBLISHED-HANDOFF-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-implementation-01/RANKING-POLICY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-implementation-01/SOURCE-ASSEMBLY-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-implementation-01/VALIDATION-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-implementation-01/VERSION-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-decision-materialization-implementation-01/WRITER-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/BASELINE-RANK-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/CANONICAL-ORDERING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/CATCH-UP-COMPATIBILITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/CURRENT-RC2-FACT-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/FACET-CATALOG.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/FACET-OPTIONALITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/FACET-OWNERSHIP-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/GEOGRAPHY-FACETS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/HISTORICAL-FACET-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/HISTORICAL-RANK-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/IMPLEMENTATION-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/LISTING-FACETS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/MEDIA-FACETS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/MIGRATION-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/NORMAL-PATH.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/POLICY-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/PROPERTY-FACETS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/PUBLIC-SEARCH-RANKING-FACET-SOURCE-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/RANK-EVOLUTION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/RANK-INPUT-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/RANK-OWNERSHIP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/SEARCH-FACET-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/SEARCH-RANK-SEMANTICS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/TEST-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-facet-source-authority-01/VISIBILITY-INTERACTION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/BASELINE-RANK.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/CERTIFICATION-NOTE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/COMMERCIAL-SIGNALS-AUDIT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/COMPATIBILITY-REPORT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/CURRENT-RC2-EVIDENCE.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/DETERMINISM.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/EMPTY-FACETS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/FACET-CATALOG-V1.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/FACETS-V1-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/FAILURE-MODES.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/GEOGRAPHY-FACETS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/IMPLEMENTATION-BOUNDARY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/MATERIALIZATION-HANDOFF.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/MEDIA-FACETS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/OWNERSHIP.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/POLICY-CONTRACT.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/POLICY-VERSIONING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/PROPERTY-FACETS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/PUBLIC-SEARCH-RANKING-POLICY-AUTHORITY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/RANK-DIRECTION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/RANK-EVOLUTION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/RANK-INPUTS.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/RANK-VALUE-DOMAIN.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/RECENCY-DECISION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/SEARCH-RANK-MEANING.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/TEST-MATRIX.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/V1-POLICY.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `docs/search/public-search-ranking-policy-authority-01/VISIBILITY-INTERACTION.md` | Include | C | Certified Foundation/Product documentation and evidence |
| `README.md` | Include | D | Release engineering source/evidence |
| `resources/css/app.css` | Include | A | Certified RC2 product/application source |
| `resources/js/authoring.js` | Include | A | Certified RC2 product/application source |
| `resources/views/authoring-workspace.blade.php` | Include | A | Certified RC2 product/application source |
| `resources/views/publication-review.blade.php` | Include | A | Certified RC2 product/application source |
| `resources/views/public-listing.blade.php` | Include | A | Certified RC2 product/application source |
| `routes/web.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Application/Materialization/ContentSeoMaterializationResult.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Application/Materialization/ContentSeoMaterializationSourceResult.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Application/Materialization/ContentSeoMaterializationSources.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Application/Materialization/ContentSeoMaterializationSourceStatus.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Application/Materialization/ContentSeoMaterializationStatus.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Application/Materialization/ContentSeoSnapshotIdentityV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Application/Materialization/Contract/CatchUpContentSeoSnapshotV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Application/Materialization/Contract/ContentSeoCanonicalPathPolicyV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Application/Materialization/Contract/ContentSeoMaterializationSourceReaderV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Application/Materialization/Contract/MaterializeContentSeoSnapshotV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Application/Materialization/DeterministicCatchUpContentSeoSnapshotV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Application/Materialization/DeterministicContentSeoCanonicalPathPolicyV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Application/Materialization/DeterministicContentSeoSnapshotMaterializerV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Domain/Model/ListingSeoDecision.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Domain/Model/PublicGeographyBreadcrumbItemV2.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Domain/Model/PublicGeographySeoSourceV2.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ContentSeo/Domain/Policy/ListingSeoDecisionPolicy.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Application/GeographySelection/Contract/GeographySelectionReaderV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Application/GeographySelection/Contract/GeographySelectionSource.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Application/GeographySelection/DeterministicGeographySelectionReader.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Application/GeographySelection/GeographySelectionCursor.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Application/GeographySelection/GeographySelectionItem.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Application/GeographySelection/GeographySelectionQuery.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Application/GeographySelection/GeographySelectionResult.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Application/GeographySelection/GeographySelectionSourceItem.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Application/GeographySelection/GeographySelectionSourceResult.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Application/GeographySelection/GeographySelectionSourceStatus.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Application/GeographySelection/GeographySelectionStatus.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Infrastructure/Persistence/PersistentPlaceIntegrity.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Infrastructure/Persistence/PlaceAliasSnapshot.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Infrastructure/Persistence/PlaceMapper.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Infrastructure/Persistence/PlaceSnapshot.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Infrastructure/Persistence/PlaceTransaction.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Infrastructure/Persistence/PostgreSql/Migrations/098_geography_places.down.sql` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Infrastructure/Persistence/PostgreSql/Migrations/098_geography_places.sql` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Infrastructure/Persistence/PostgreSql/PostgreSqlGeographySelectionSource.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Infrastructure/Persistence/PostgreSql/PostgreSqlPlaceRepository.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/Geography/Infrastructure/Persistence/PostgreSql/PostgreSqlPlaceTransaction.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/ListingLifecycle/Application/Creation/DeterministicCreateListingDraftV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/AddressIdentity/AddressIdentityIssuanceResult.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/AddressIdentity/AddressIdentityIssuanceStatus.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/AddressIdentity/AddressIntentId.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/AddressIdentity/Contract/AddressIdentityIssuerV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/AddressIdentity/DeterministicAddressIdentityIssuerV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/AuthoringPersistence/PropertyAuthoringSourceCompleteness.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/AuthoringPersistence/PropertyAuthoringState.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/BusinessYear/BusinessYearResolutionResult.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/BusinessYear/BusinessYearResolutionStatus.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/BusinessYear/Contract/BusinessYearAuthorityV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/BusinessYear/PropertyDecisionOccurredAt.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/BusinessYear/UtcCalendarBusinessYearAuthorityV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/Promotion/Contract/PromoteAuthoredPropertyV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/Promotion/Contract/PromotionCommandLedger.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/Promotion/Contract/PromotionTransaction.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/Promotion/DeterministicPromoteAuthoredPropertyV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/Promotion/PromoteAuthoredPropertyCommand.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/Promotion/PromoteAuthoredPropertyResult.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/Promotion/PromoteAuthoredPropertyStatus.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/Promotion/PromotionArguments.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/Promotion/PromotionCommandChecksum.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Application/Promotion/PromotionCommandRecord.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Domain/Policy/GeographicPlaceAddressabilityPolicy.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Domain/ValueObject/GeographicPlaceAddressability.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Infrastructure/Geography/GeographyBackedGeographicPlaceCatalog.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/099_property_authoring_source_completeness.down.sql` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/099_property_authoring_source_completeness.sql` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/100_property_promotion.down.sql` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/Migrations/100_property_promotion.sql` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/PostgreSqlPromotionCommandLedger.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/PostgreSqlPromotionParticipantTransaction.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/PostgreSqlPromotionTransaction.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Infrastructure/Persistence/PostgreSql/PostgreSqlPropertyAuthoringStore.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/RealEstateCatalog/Infrastructure/Persistence/PropertyAuthoringMapper.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/SearchDiscovery/Application/Materialization/Contract/CatchUpPublicSearchDecisionV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/SearchDiscovery/Application/Materialization/Contract/MaterializePublicSearchDecisionV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/SearchDiscovery/Application/Materialization/Contract/PublicSearchMaterializationSourceReaderV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/SearchDiscovery/Application/Materialization/Contract/PublicSearchRankingPolicyV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/SearchDiscovery/Application/Materialization/DeterministicCatchUpPublicSearchDecisionV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/SearchDiscovery/Application/Materialization/DeterministicPublicSearchDecisionMaterializerV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/SearchDiscovery/Application/Materialization/DeterministicPublicSearchRankingPolicyV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/SearchDiscovery/Application/Materialization/PublicSearchDecisionIdentityV1.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/SearchDiscovery/Application/Materialization/PublicSearchMaterializationResult.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/SearchDiscovery/Application/Materialization/PublicSearchMaterializationSourceResult.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/SearchDiscovery/Application/Materialization/PublicSearchMaterializationSources.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/SearchDiscovery/Application/Materialization/PublicSearchMaterializationSourceStatus.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/SearchDiscovery/Application/Materialization/PublicSearchMaterializationStatus.php` | Include | A | Certified RC2 product/application source |
| `src/Modules/SearchDiscovery/Application/Materialization/PublicSearchRankingDecisionV1.php` | Include | A | Certified RC2 product/application source |
| `tests/Architecture/AddressIdentityArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/AuthoringDraftResumeArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/BusinessYearAuthorityArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/ContentSeoSnapshotMaterializationArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/GeographicPlaceCatalogArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/GeographyPlacePersistenceArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/GeographySelectionArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/ListingPublicationCommandGatewayArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/MediaAuthoringPublicSurfaceArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/PropertyAuthoringGeographySelectionArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/PropertyAuthoringSourceCompletenessArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/PropertyListingAuthoringOperationsArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/PublicGeographyMaterializationArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/PublicGeographyV2ConsumerAlignmentArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/PublicPropertyPromotionArchitectureTest.php` | Include | B | Certified test |
| `tests/Architecture/PublicSearchDecisionMaterializationArchitectureTest.php` | Include | B | Certified test |
| `tests/Feature/ActiveGenerationBootstrapBindingTest.php` | Include | B | Certified test |
| `tests/Feature/AddressIdentityBindingTest.php` | Include | B | Certified test |
| `tests/Feature/AuthoringDraftResumeHttpTest.php` | Include | B | Certified test |
| `tests/Feature/BusinessYearAuthorityBindingTest.php` | Include | B | Certified test |
| `tests/Feature/ContentSeoSnapshotMaterializationBindingTest.php` | Include | B | Certified test |
| `tests/Feature/GeographicPlaceCatalogBindingTest.php` | Include | B | Certified test |
| `tests/Feature/GeographyPlacePersistenceBindingTest.php` | Include | B | Certified test |
| `tests/Feature/GeographySelectionBindingTest.php` | Include | B | Certified test |
| `tests/Feature/PropertyAuthoringGeographySelectionHttpTest.php` | Include | B | Certified test |
| `tests/Feature/PublicationReviewExperienceTest.php` | Include | B | Certified test |
| `tests/Feature/PublicAuthoringIntegrationSecurityTest.php` | Include | B | Certified test |
| `tests/Feature/PublicMediaBinaryDeliveryHttpTest.php` | Include | B | Certified test |
| `tests/Feature/PublicMediaMaterializationBindingTest.php` | Include | B | Certified test |
| `tests/Feature/PublicPropertyPromotionBindingTest.php` | Include | B | Certified test |
| `tests/Feature/PublicSearchDecisionMaterializationBindingTest.php` | Include | B | Certified test |
| `tests/Feature/PublicWebAdapterTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/AuthoringDraftResume/PostgreSqlAuthoringDraftResumeTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/AuthoringOperations/PostgreSqlAuthoringOperationsTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/AuthoringPersistence/PostgreSqlAuthoringPersistenceTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/AuthoringPersistence/PostgreSqlPropertyAuthoringSourceCompletenessMigrationTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/GeographicPlaceCatalog/PostgreSqlGeographicPlaceCatalogTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/GeographyPlacePersistence/PostgreSqlPlaceRepositoryTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/GeographySelection/PostgreSqlGeographySelectionSourceTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/ListingPublicationEventIntegration/PostgreSqlListingPublicationEventIntegrationTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/PublicGeographySource/PostgreSqlPublicGeographySourceTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/PublicMediaMaterialization/PostgreSqlPublicMediaMaterializationV2Test.php` | Include | B | Certified test |
| `tests/PostgreSQL/PublicProjectionOutbox/PostgreSqlTransactionalRuntimeCompositionTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/PublicPropertyPromotion/PostgreSqlPropertyPromotionMigrationTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/PublicPropertyPromotion/PostgreSqlPublicPropertyPromotionTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/PublicPropertySourceCompleteness/PostgreSqlSourceAssemblyCertificationTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/SearchDecisionMaterialization/PostgreSqlPublicSearchDecisionMaterializationTest.php` | Include | B | Certified test |
| `tests/PostgreSQL/Support/PostgreSqlTestEnvironment.php` | Include | B | Certified test |
| `tests/Support/PublicListingReadModelFixture.php` | Include | B | Certified test |
| `tests/Unit/ActiveGenerationBootstrap/DeterministicActiveProjectionGenerationBootstrapV1Test.php` | Include | B | Certified test |
| `tests/Unit/AddressIdentity/AddressIdentityIssuerV1Test.php` | Include | B | Certified test |
| `tests/Unit/Architecture/ActiveGenerationBootstrapArchitectureTest.php` | Include | B | Certified test |
| `tests/Unit/Architecture/PublicMediaBinaryDeliveryArchitectureTest.php` | Include | B | Certified test |
| `tests/Unit/Architecture/PublicMediaMaterializationArchitectureTest.php` | Include | B | Certified test |
| `tests/Unit/AuthoringDraftResume/DeterministicAuthoringDraftResumeReaderV1Test.php` | Include | B | Certified test |
| `tests/Unit/AuthoringOperations/AuthoringSubmissionHandoffTest.php` | Include | B | Certified test |
| `tests/Unit/BusinessYear/BusinessYearAuthorityV1Test.php` | Include | B | Certified test |
| `tests/Unit/ContentSeoSnapshotMaterialization/ContentSeoSnapshotIdentityAndCanonicalPolicyTest.php` | Include | B | Certified test |
| `tests/Unit/GeographicPlaceCatalog/GeographicPlaceAddressabilityPolicyTest.php` | Include | B | Certified test |
| `tests/Unit/GeographicPlaceCatalog/GeographyBackedGeographicPlaceCatalogTest.php` | Include | B | Certified test |
| `tests/Unit/GeographySelection/DeterministicGeographySelectionReaderTest.php` | Include | B | Certified test |
| `tests/Unit/ListingPublicationEventTransport/ListingPublicationOutboxCompatibilityTest.php` | Include | B | Certified test |
| `tests/Unit/Modules/ContentSeo/ListingSeoDecisionPolicyTest.php` | Include | B | Certified test |
| `tests/Unit/Modules/Geography/PlaceMapperTest.php` | Include | B | Certified test |
| `tests/Unit/PostgreSql/PostgreSqlTestEnvironmentIsolationTest.php` | Include | B | Certified test |
| `tests/Unit/ProjectionRuntimeSource/CertifiedPublicListingProjectionSourceTest.php` | Include | B | Certified test |
| `tests/Unit/Projections/SeoListingProjectionTest.php` | Include | B | Certified test |
| `tests/Unit/PropertyAuthoringGeographySelection/GeographySelectionReplayValidatorV1Test.php` | Include | B | Certified test |
| `tests/Unit/PropertyAuthoringPublicSurface/DeterministicPropertyAuthoringPublicSurfaceTest.php` | Include | B | Certified test |
| `tests/Unit/PropertyAuthoringSourceCompleteness/PropertyAuthoringSourceCompletenessTest.php` | Include | B | Certified test |
| `tests/Unit/PublicGeographyMaterialization/PublicGeographyDecisionV2AssemblerTest.php` | Include | B | Certified test |
| `tests/Unit/PublicGeographySource/PostgreSqlPublicGeographyMapperV2Test.php` | Include | B | Certified test |
| `tests/Unit/PublicGeographySource/PublicGeographyDecisionTest.php` | Include | B | Certified test |
| `tests/Unit/PublicMediaBinaryDelivery/DeterministicPublicMediaBinaryResolverV1Test.php` | Include | B | Certified test |
| `tests/Unit/PublicMediaMaterialization/DeterministicPublicMediaDecisionMaterializerV2Test.php` | Include | B | Certified test |
| `tests/Unit/PublicPropertyPromotion/DeterministicPromoteAuthoredPropertyV1Test.php` | Include | B | Certified test |
| `tests/Unit/PublicSearchDecisionMaterialization/ListingPublishedSearchHandoffTest.php` | Include | B | Certified test |
| `tests/Unit/PublicSearchDecisionMaterialization/PublicSearchDecisionMaterializerTest.php` | Include | B | Certified test |
| `tests/Unit/ReadModels/PublicListingReadModelTest.php` | Include | B | Certified test |
