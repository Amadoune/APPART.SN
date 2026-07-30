# Phase 5.0A — Ownership Matrix

## 1. Owners uniques

Un owner est une responsabilité architecturale, pas une personne. Chaque ligne
possède exactement un Aggregate Owner, un Event Owner, un Projection Owner, un
Outbox Owner logique et un HTTP Owner.

| Capacité | Aggregate Owner | Event Owner | Projection Owner | Outbox Owner | HTTP Owner |
|---|---|---|---|---|---|
| Account & Access | IdentityAccess/Account | IdentityAccess | IdentityAccess private views | IdentityAccess | IdentityAccess adapter |
| Professional Profile | Professionals/Professional | Professionals | Professionals public profile | Professionals | Professionals adapter |
| Place | Geography/Place | Geography | Public Geography | Geography | Geography adapter |
| Property | RealEstateCatalog/Property | RealEstateCatalog | Public Listing source/PUB | RealEstateCatalog | RealEstateCatalog adapter |
| Listing | ListingLifecycle/Listing | ListingLifecycle | PUB | ListingLifecycle | ListingLifecycle adapter |
| Media | Media/MediaCollection | Media | Public Media/PUB | Media | Media adapter |
| Lead | ContactsLeads/Lead | ContactsLeads | Contacts private reporting | ContactsLeads | ContactsLeads adapter |
| Reservation | ReservationLifecycle/Reservation state | ReservationLifecycle | Reservation private calendar | ReservationLifecycle | ReservationLifecycle adapter |
| Moderation | ModerationReports/ModerationCase | ModerationReports | Moderation queues | ModerationReports | ModerationReports adapter |
| Product/Order/Payment | MonetizationPayments | MonetizationPayments | Finance/admin views | MonetizationPayments | MonetizationPayments + webhook adapters |
| Search | SearchDiscovery/SearchIndex | SearchDiscovery pour ses rebuild events | SearchDiscovery | SearchDiscovery si événements propres | SearchDiscovery query adapter |
| Content/SEO | ContentSeo/Content + SeoProjection | ContentSeo | ContentSeo | ContentSeo | ContentSeo public/admin adapters |
| Favorites | Favorites/FavoriteCollection | Favorites | Favorites private view | Favorites | Favorites adapter |
| Notifications | Notifications/Preference, DeliveryAttempt | Notifications pour outcomes | Notifications delivery view | Notifications | Notifications preference adapter |
| Admin Audit | AdministrationAudit/AdministrativeAction | AdministrationAudit | Audit read model | AdministrationAudit | Administration adapter |
| Legacy Migration | LegacyMigration/Batch, Checkpoint | LegacyMigration | Reconciliation report | LegacyMigration | CLI/admin restricted adapter |
| Public listing view | aucun Aggregate | aucun événement métier propre | PUB | aucun hors delivery technique | PublicListingController |

## 2. Ownership physique certifié

| Zone physique | Owner logique | Règle |
|---|---|---|
| migrations 001–004 | Aggregate correspondant | gelées |
| migrations 005–014 | projection publique, sources et ContentSeo | gelées/certifiées |
| migrations 015–043 | lifecycle/inbox/outbox owner nommé par migration | gelées selon certification |
| `public_projection_outbox` | infrastructure physique partagée | chaque producteur possède uniquement ses event types/lignes |
| inboxes dédiées | consommateur nommé | aucun autre writer |
| `PublicProjectionRuntimeServiceProvider` | composition plateforme | modification seulement par phase runtime dédiée |
| routes lifecycle existantes | HTTP owner du domaine | gelées |
| projection publique | PUB | lecture seulement pour les autres domaines |

## 3. RACI architectural

| Décision | A (accountable) | R (responsible) | C | I |
|---|---|---|---|---|
| invariant Aggregate | Aggregate Owner | équipe domaine | consumers | architecture |
| nom/version event | Event Owner | équipe domaine | Projection/Outbox owners | consumers |
| schéma projection | Projection Owner | équipe projection | Event Owners | HTTP owner |
| sérialisation Outbox | Outbox Owner logique + plateforme | équipe domaine | transport owner | consumers |
| endpoint/status HTTP | HTTP Owner | équipe adapter | Aggregate Owner | clients |
| dépendance cross-domain | architecture | consumer | producer owner | QA/ops |
| amendement gelé | autorité de certification | owner concerné | tous consumers | projet |
| rétention/PII | privacy owner | domaine détenteur | sécurité/juridique | ops |

## 4. Collisions explicitement résolues

- `PublicProjectionRuntimeServiceProvider` n'est pas owner métier des neuf
  lifecycles qu'il compose.
- `public_projection_outbox` n'est pas owner des événements ; il en assure la
  durabilité.
- AdministrationAudit trace une sanction, mais ModerationReports décide et
  l'Aggregate ciblé applique.
- SearchDiscovery et ContentSeo possèdent leurs projections, jamais Listing,
  Property ou Geography.
- Product appartient exclusivement à MonetizationPayments conformément à
  ADR-1006 ; aucun catalogue Listing ne peut le muter.
- Historical Account reste sous IdentityAccess ; LegacyMigration n'en devient
  pas owner.
