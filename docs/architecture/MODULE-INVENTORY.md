# Module Inventory — baseline Sprint 2.2

État observé le 17 juillet 2026 dans `src/Modules` et `tests/Unit/Modules`. Cet inventaire décrit le code présent ; il ne préjuge pas de la future persistance.

## Synthèse

| Module | Aggregate Root(s) | Modèles | Value Objects | Événements | Use Cases | Ports/Contracts | Fakes | Tests métier |
|---|---|---:|---:|---:|---:|---:|---:|---:|
| AdministrationAudit | AdministrativeAction | 4 | 9 | 8 | 6 | 1 | 1 | 29 |
| ContactsLeads | Lead | 2 | 24 | 7 | 5 | 3 | 3 | 24 |
| ContentSeo | SeoProjection | 8 | 17 | 8 | 6 | 4 | 4 | 39 |
| Geography | Place | 3 | 6 | 6 | 5 | 2 | 1 | 53 |
| IdentityAccess | Account | 5 | 9 | 14 | 13 | 1 | 1 | 37 |
| LegacyMigration | — | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| ListingLifecycle | Listing | 3 | 13 | 13 | 11 | 2 | 2 | 16 |
| Media | MediaCollection | 2 | 9 | 9 | 8 | 2 | 2 | 34 |
| ModerationReports | ModerationCase | 5 | 16 | 8 | 6 | 2 | 2 | 19 |
| MonetizationPayments | Order, Payment, Product | 8 | 17 | 9 | 8 | 5 | 3 | 40 |
| Professionals | Professional | 3 | 8 | 10 | 8 | 1 | 1 | 30 |
| RealEstateCatalog | Property | 2 | 13 | 8 | 5 | 2 | 2 | 34 |
| SearchDiscovery | SearchIndex | 7 | 15 | 8 | 4 | 4 | 4 | 30 |
| **Total** | **14** | **52** | **156** | **108 fichiers** | **85 fichiers** | **29** | **28** | **385 méthodes** |

Les nombres d’événements incluent interfaces, abstractions et métadonnées. Les fichiers `*UseCase`, `*Sources`, `*Mutation` et `AccountAction` sont des bases/supports d’orchestration inclus dans le total. Il n’existe aucun dossier ou type `DTO` explicite.

## Détail exhaustif

### AdministrationAudit

- Aggregate : `AdministrativeAction`.
- Entités/objets internes : `Approval`, `AuditEntry`, `Decision`.
- Value Objects : `ActionType`, `ActorId`, `AdministrativeActionId`, `AdministrativeActionStatus`, `ApprovalId`, `AuditReason`, `DecisionId`, `DecisionOutcome`, `TargetResourceId`.
- Événements : `AdministrativeActionApproved`, `AdministrativeActionRecorded`, `AdministrativeActionRejected`, `AuditEntryRecorded`, `FourEyesSatisfied`; support : `AbstractAdministrativeActionEvent`, `AdministrativeActionEvent`, `AdministrativeActionEventMetadata`.
- Use Cases : `AddAuditReason`, `ApproveAdministrativeAction`, `CreateAdministrativeAction`, `RecordAdministrativeAction`, `RejectAdministrativeAction`; support : `AdministrativeActionUseCase`.
- Registry : `AdministrativeActionRegistry`. Fake : `FakeAdministrativeActionRegistry`.

### ContactsLeads

- Aggregate : `Lead`. Entité interne : `LeadHistoryEntry`.
- Value Objects : `AdvertiserEligibility`, `AdvertiserEligibilityEvidence`, `AdvertiserId`, `ConsentDecision`, `ConsentProof`, `ConsentPurpose`, `ContactChannel`, `ContactCoordinates`, `ContactMessage`, `ContactSubject`, `EligibilityRevision`, `LeadDeduplicationClaim`, `LeadEligibilityProof`, `LeadId`, `LeadRejectionReason`, `LeadSignature`, `LeadStatus`, `LeadTimestamp`, `ListingContactability`, `ListingContactEvidence`, `ListingId`, `VisitorIdentity`, `VisitorIdentityId`, `VisitorIdentityType`.
- Événements : `LeadClosed`, `LeadCreated`, `LeadDelivered`, `LeadRejected`; support : `AbstractLeadEvent`, `LeadEvent`, `LeadEventMetadata`.
- Use Cases : `CloseLead`, `CreateLead`, `DeliverLead`, `RejectLead`; support : `LeadUseCase`.
- Ports : `LeadRegistry`, `AdvertiserCatalog`, `ListingCatalog`. Fakes correspondants : `FakeLeadRegistry`, `FakeAdvertiserCatalog`, `FakeListingCatalog`.

### ContentSeo

- Aggregate/projection : `SeoProjection`.
- Entités/objets internes : `CanonicalHistoryEntry`, `ListingSeoSource`, `PropertySeoSource`, `SearchSeoSource`, `SeoDocument`, `SeoMaterial`, `StructuredData`.
- Value Objects : `CanonicalDisposition`, `CanonicalUrl`, `ListingId`, `ListingSeoState`, `MetaDescription`, `PropertySeoState`, `RobotsPolicy`, `SearchSeoState`, `SeoFailSafeReason`, `SeoProjectionId`, `SeoProjectionState`, `SeoSourceKind`, `SeoSourceRevision`, `SeoSourceRevisions`, `SeoTitle`, `SitemapPriority`, `StructuredDataType`.
- Événements : `CanonicalChanged`, `SeoFailSafeApplied`, `SeoProjectionGenerated`, `SeoProjectionUpdated`, `SitemapChanged`; support : `AbstractSeoEvent`, `SeoEvent`, `SeoEventMetadata`.
- Use Cases : `ChangeCanonical`, `GenerateSeoProjection`, `RemoveSeoProjection`, `UpdateSeoProjection`; support : `SeoSources`, `SeoUseCase`.
- Ports : `SeoProjectionRegistry`, `ListingCatalog`, `PropertyCatalog`, `SearchCatalog`; quatre fakes homonymes préfixés `Fake`.

### Geography

- Aggregate : `Place`. Entités/objets internes : `AdministrativeDivision`, `GeographicAlias`.
- Value Objects : `Coordinates`, `CountryCode`, `PlaceCode`, `PlaceId`, `PlaceName`, `PlaceType`.
- Événements : `PlaceCreated`, `PlaceDisabled`, `PlaceEnabled`, `PlaceMerged`, `PlaceRenamed`; contrat support : `PlaceEvent`.
- Use Cases : `CreatePlace`, `DisablePlace`, `EnablePlace`, `MergePlace`, `RenamePlace`.
- Contracts : `PlaceRegistry` (Application) et `PlaceLookup` (Domain). Fake : `FakePlaceRegistry`. Service de domaine : `PlaceHierarchy`.

### IdentityAccess

- Aggregate : `Account`. Entités internes : `Consent`, `Credential`, `RoleAssignment`, `Verification`.
- Value Objects : `AccountId`, `ConsentPurpose`, `EmailAddress`, `PasswordHash`, `PersonName`, `PhoneNumber`, `RoleId`, `VerificationChannel`, `VerificationToken`.
- Événements : `AccountReactivated`, `AccountRegistered`, `AccountSuspended`, `ConsentGranted`, `ConsentWithdrawn`, `EmailVerified`, `PasswordChanged`, `PhoneVerified`, `RoleGranted`, `RoleRevoked`, `VerificationReplaced`; support : `AbstractAccountEvent`, `AccountEvent`, `AccountEventMetadata`.
- Use Cases : `ChangePassword`, `GrantConsent`, `GrantRole`, `ReactivateAccount`, `RegisterAccount`, `ReplaceEmailVerification`, `ReplacePhoneVerification`, `RevokeRole`, `SuspendAccount`, `VerifyEmail`, `VerifyPhone`, `WithdrawConsent`; support : `AccountAction`.
- Registry : `AccountRegistry`. Fake : `FakeAccountRegistry`.

### LegacyMigration

Enveloppe physique vide (`.gitkeep`) : aucun Aggregate, objet, événement, port, cas d’usage, fake ou test.

### ListingLifecycle

- Aggregate : `Listing`. Entités/objets internes : `ListingRevision`, `TransitionEvidence`.
- Value Objects : `ActorId`, `ExpirationDate`, `ListingId`, `ListingRevisionId`, `ListingStatus`, `PropertyAvailability`, `PropertyId`, `PublicationReason`, `RenewalRoute`, `SuspensionReason`, `TransitionOrigin`, `TransitionReason`, `TransitionTrigger`.
- Événements : `ListingArchived`, `ListingChangesRequested`, `ListingDraftCreated`, `ListingExpired`, `ListingPublished`, `ListingRejected`, `ListingSentToReview`, `ListingSubmitted`, `ListingSuspended`, `ListingWithdrawn`; support : `AbstractListingEvent`, `ListingEvent`, `ListingEventMetadata`.
- Use Cases : `ArchiveListing`, `CreateDraft`, `ExpireListing`, `PublishListing`, `RejectListing`, `RequestChanges`, `SendToReview`, `SubmitListing`, `SuspendListing`, `WithdrawListing`; support : `ListingUseCase`.
- Ports : `ListingRegistry`, `PropertyCatalog`. Fakes : `FakeListingRegistry`, `FakePropertyCatalog`.

### Media

- Aggregate : `MediaCollection`. Entité interne : `MediaItem`.
- Value Objects : `MediaCaption`, `MediaChecksum`, `MediaCollectionId`, `MediaId`, `MediaOrder`, `MediaSource`, `MediaStatus`, `MediaType`, `PropertyId`.
- Événements : `MediaAdded`, `MediaArchived`, `MediaCaptionChanged`, `MediaMarkedPrimary`, `MediaRemoved`, `MediaReordered`; support : `AbstractMediaCollectionEvent`, `MediaCollectionEvent`, `MediaCollectionEventMetadata`.
- Use Cases : `AddMedia`, `ArchiveMedia`, `ChangeCaption`, `CreateMediaCollection`, `MarkPrimaryMedia`, `RemoveMedia`, `ReorderMedia`; support : `MediaCollectionUseCase`.
- Ports : `MediaCollectionRegistry`, `PropertyCatalog`. Fakes : `FakeMediaCollectionRegistry`, `FakePropertyCatalog`.

### ModerationReports

- Aggregate : `ModerationCase`. Entités/objets internes : `ModerationDecision`, `ModerationFinding`, `Report`, `ReportValidationAssessment`.
- Value Objects : `DecisionId`, `DecisionType`, `FindingId`, `FindingSeverity`, `ListingEligibility`, `ListingId`, `ModerationCaseId`, `ModerationRationale`, `ModeratorId`, `ReportAdmissibility`, `ReportAuthenticity`, `ReportCompleteness`, `ReporterId`, `ReportHandlingDecision`, `ReportId`, `ReportReason`.
- Événements : `DecisionIssued`, `FindingRecorded`, `ModerationCaseClosed`, `ReportCreated`, `ReportValidated`; support : `AbstractModerationCaseEvent`, `ModerationCaseEvent`, `ModerationCaseEventMetadata`.
- Use Cases : `CloseCase`, `CreateReport`, `IssueDecision`, `RecordFinding`, `ValidateReport`; support : `ModerationCaseUseCase`.
- Ports : `ModerationCaseRegistry`, `ListingCatalog`. Fakes : `FakeModerationCaseRegistry`, `FakeListingCatalog`.

### MonetizationPayments

- Aggregates : `Order`, `Payment`, `Product`.
- Entités/objets internes : `CommercialBenefit`, `OrderHistoryEntry`, `OrderLine`, `PaymentAttempt`, `ProductHistoryEntry`.
- Value Objects : `BenefitPeriod`, `BenefitStatus`, `BenefitType`, `Currency`, `Money`, `OccurredAt`, `OrderId`, `OrderStatus`, `PaymentId`, `PaymentMethod`, `PaymentProof`, `PaymentRegistration`, `PaymentRegistrationStatus`, `PaymentStatus`, `ProductId`, `RefundProof`, `TransactionReference`.
- Événements : `BenefitExpired`, `BenefitGranted`, `BenefitRevoked`, `OrderCreated`, `PaymentAuthorized`, `PaymentCaptured`, `PaymentFailed`, `PaymentRefunded`; support : `PaymentEvent`.
- Use Cases : `AuthorizePayment`, `CapturePayment`, `CreateOrder`, `ExpireBenefit`, `FailPayment`, `GrantBenefit`, `RefundPayment`; support : `PaymentMutation`.
- Ports : `OrderRegistry`, `PaymentRegistry`, `PaymentCatalog`, `ProductCatalog`, `RefundTransaction`. Fakes : `FakeOrderRegistry`, `FakePaymentRegistry`, `FakeProductCatalog`. Aucun fake dédié à `PaymentCatalog` ou `RefundTransaction`.

### Professionals

- Aggregate : `Professional`. Entités internes : `Establishment`, `RepresentativeMandate`.
- Value Objects : `EstablishmentId`, `EstablishmentName`, `MandateId`, `MandateRole`, `ProfessionalId`, `ProfessionalName`, `RegistrationNumber`, `RepresentativeId`.
- Événements : `EstablishmentAdded`, `EstablishmentRemoved`, `MandateGranted`, `MandateRevoked`, `ProfessionalReactivated`, `ProfessionalRegistered`, `ProfessionalSuspended`; support : `AbstractProfessionalEvent`, `ProfessionalEvent`, `ProfessionalEventMetadata`.
- Use Cases : `AddEstablishment`, `GrantMandate`, `ReactivateProfessional`, `RegisterProfessional`, `RemoveEstablishment`, `RevokeMandate`, `SuspendProfessional`; support : `ProfessionalUseCase`.
- Registry : `ProfessionalRegistry`. Fake : `FakeProfessionalRegistry`.

### RealEstateCatalog

- Aggregate : `Property`. Entité interne : `Address`.
- Value Objects : `AddressId`, `AddressLine`, `BathroomCount`, `BusinessYear`, `ConstructionYear`, `GeographicPlaceId`, `GeographicPlaceStatus`, `PropertyId`, `PropertyReference`, `PropertyStatus`, `PropertyType`, `RoomCount`, `SurfaceArea`.
- Événements : `AddressChanged`, `PropertyArchived`, `PropertyRegistered`, `PropertyUpdated`, `SurfaceChanged`; support : `AbstractPropertyEvent`, `PropertyEvent`, `PropertyEventMetadata`.
- Use Cases : `ArchiveProperty`, `ChangeAddress`, `RegisterProperty`, `UpdateProperty`; support : `PropertyUseCase`.
- Ports : `PropertyRegistry`, `GeographicPlaceCatalog`. Fakes : `FakePropertyRegistry`, `FakeGeographicPlaceCatalog`.

### SearchDiscovery

- Aggregate/projection : `SearchIndex`.
- Entités/objets internes : `ListingProjectionSource`, `MediaProjectionSource`, `PropertyProjectionSource`, `SearchDocument`, `SearchFacet`, `SearchProjection`.
- Value Objects : `FreshnessDecision`, `ListingId`, `ListingSearchState`, `MediaSearchState`, `ProjectionChange`, `ProjectionState`, `PropertySearchState`, `SearchDocumentId`, `SearchFacetKey`, `SearchFacetValue`, `SearchIndexId`, `SearchRank`, `SourceKind`, `SourceRevision`, `SourceRevisionSet`.
- Événements : `SearchDocumentIndexed`, `SearchDocumentRebuilt`, `SearchDocumentRemoved`, `SearchDocumentUpdated`, `SearchVisibilityChanged`; support : `AbstractSearchIndexEvent`, `SearchIndexEvent`, `SearchIndexEventMetadata`.
- Use Cases : `IndexListing`, `UpdateIndex`; support : `ProjectionSources`, `SearchIndexUseCase`.
- Ports : `SearchIndexRegistry`, `ListingCatalog`, `MediaCatalog`, `PropertyCatalog`; quatre fakes homonymes préfixés `Fake`.

## DTO et contrats

- DTO explicites : aucun.
- Registries d’Aggregate : 13 (`AdministrativeAction`, `Lead`, `SeoProjection`, `Place`, `Account`, `Listing`, `MediaCollection`, `ModerationCase`, `Order`, `Payment`, `Professional`, `Property`, `SearchIndex`).
- Autres ports applicatifs : 15 Catalogs/Transactions, auxquels s’ajoute le contrat de domaine `PlaceLookup`.
- `Product` est reconstructible et versionné mais seulement exposé en lecture par `ProductCatalog`; l’absence de Registry d’écriture doit être tranchée avant son adaptateur de persistance.
