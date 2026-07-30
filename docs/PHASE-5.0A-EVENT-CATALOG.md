# Phase 5.0A — Event Catalog

## 1. Catalogue certifié des lifecycles

| Event Owner | Événements produits | Consommateurs autorisés |
|---|---|---|
| ListingLifecycle | `listing.publication.submitted`, `resubmitted`, `review_started`, `material_change_review_started`, `renewal_review_started`, `republication_review_started`, `published`, `changes_requested`, `rejected`, `withdrawn`, `suspended`, `reinstated`, `expired`, `renewed`, `archived` | PUB, Search, SEO, Notifications, Moderation read side |
| RealEstateCatalog | `property.lifecycle.activated`, `archived`, `maintenance_started`, `maintenance_completed`, `marked_unavailable`, `availability_restored`, `decommissioned` | Listing, PUB, Search, Notifications, Reservations |
| ReservationLifecycle | `reservation.lifecycle.submitted`, `cancelled_from_draft`, `confirmed`, `rejected`, `cancelled_from_requested`, `expired_from_requested`, `started`, `cancelled_from_confirmed`, `expired_from_confirmed`, `completed`, `cancelled_in_progress` | Notifications, Admin reporting |
| ContactsLeads | `lead.lifecycle.delivered`, `rejected`, `closed` | Notifications, advertiser reporting, Audit |
| Professionals | `professional.status.suspended`, `reactivated` | Listing eligibility, Lead, PUB, Search, Notifications |
| Media | `media.item.lifecycle.removed`, `archived` | PUB, Search, SEO, Notifications |
| AdministrationAudit | `administrative.action.lifecycle.recorded`, `approval_requested`, `approved`, `rejected` | Audit read model, Notifications |
| Geography | `place.lifecycle.enabled`, `disabled`, `merged` | Public Geography, Property validation, Search, SEO, Migration reconciliation |
| IdentityAccess | `account.status.suspended`, `reactivated` | authorization, Professionals, Listing, Lead, Reservation, Notifications |

Les enveloppes, payloads V1, checksums, metadata, serializers, routing, inbox,
delivery et Outbox associés sont gelés avec leurs certifications respectives.

## 2. Événements de domaine déjà présents hors catalogues lifecycle

| Owner | Familles observées dans le code | Statut |
|---|---|---|
| IdentityAccess | AccountRegistered, Email/PhoneVerified, PasswordChanged, RoleGranted/Revoked, ConsentGranted/Withdrawn, VerificationReplaced | domaine présent ; transport à cadrer |
| Professionals | ProfessionalRegistered, EstablishmentAdded/Removed, MandateGranted/Revoked | domaine présent ; transport à cadrer |
| RealEstateCatalog | PropertyRegistered/Updated/Archived, AddressChanged, SurfaceChanged | aggregate historique ; distinguer du lifecycle gelé |
| ListingLifecycle | ListingDraftCreated, Submitted, SentToReview, Published, ChangesRequested, Rejected, Suspended, Expired, Withdrawn, Archived | aggregate historique ; ne pas doubler le catalogue publication |
| Media | MediaAdded/Removed/Archived/Reordered/MarkedPrimary/CaptionChanged | aggregate historique ; déduplication sémantique requise |
| ContactsLeads | LeadCreated/Delivered/Rejected/Closed | aggregate historique ; `LeadCreated` reste à intégrer |
| Geography | PlaceCreated/Renamed/Enabled/Disabled/Merged | aggregate historique ; transport lifecycle partiel |
| AdministrationAudit | Recorded/Approved/Rejected, AuditEntryRecorded, FourEyesSatisfied | aggregate historique |
| ContentSeo | SeoProjectionGenerated/Updated, CanonicalChanged, SitemapChanged, SeoFailSafeApplied | projection ; transport à cadrer |
| SearchDiscovery | SearchDocumentIndexed/Updated/Rebuilt/Removed, SearchVisibilityChanged | projection ; événements techniques, non décisions sources |
| ModerationReports | ReportCreated/Validated, FindingRecorded, DecisionIssued, ModerationCaseClosed | non certifiés |
| MonetizationPayments | OrderCreated, PaymentAuthorized/Captured/Failed/Refunded, BenefitGranted/Expired/Revoked | non certifiés et conditionnels |

Avant exposition, chaque famille historique doit choisir une seule autorité
événementielle et éliminer tout doublon avec le catalogue lifecycle.

## 3. Catalogue futur réservé

Ces noms sont des intentions de planification, pas de nouveaux contrats.

| Futur Event Owner | Intentions d'événements | Consommateurs pressentis |
|---|---|---|
| ModerationReports | report received/validated, decision issued, case closed | Listing/Media/Account command handlers, Audit, Notifications |
| MonetizationPayments | order created, payment authorized/captured/failed/refunded, benefit granted/revoked/expired | entitlement read model, Audit, Notifications |
| Favorites | favorite added/removed, collection cleared | analytics minimisée uniquement |
| Notifications | delivery queued/sent/failed/suppressed | ops, user delivery history |
| LegacyMigration | batch started/completed/failed, record quarantined, reconciliation passed | ops/audit uniquement |
| ContentSeo | content published/unpublished, sitemap generation completed | public web, search engines adapter |

## 4. Règles de production et consommation

1. L'Event Owner est l'unique producteur du nom et du payload.
2. Un consumer ne déduit jamais une décision métier absente du payload.
3. Livraison au moins une fois : inbox/idempotence obligatoires.
4. Ordre garanti seulement par stream d'Aggregate, jamais globalement.
5. PII et secrets exclus ; références minimales et pseudonymisées.
6. Évolution additive compatible en V1 ; changement sémantique en V2.
7. Unknown event/version : quarantaine, métrique et replay contrôlé.
8. Projection failure n'annule jamais la transaction métier déjà commitée.
9. Un nouveau consumer d'un événement gelé peut être additif ; modifier le
   producteur ou le contrat exige amendement.
