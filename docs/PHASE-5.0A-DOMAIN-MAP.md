# Phase 5.0A — Domain Map

## 1. Carte directrice

| ID | Bounded context | Type | Source de vérité | Entrées autorisées | Sorties |
|---|---|---|---|---|---|
| IAM | Identity & Access | support critique | Account | commandes utilisateur/admin | account events, status lookup |
| PRO | Professionals | cœur B2B | Professional | account reference, commandes métier | profile/status events |
| GEO | Geography | référentiel | Place | commandes admin contrôlées | place events, public geography |
| REC | Real Estate Catalog | cœur | Property | Geography lookup, commandes owner | property events |
| LST | Listing Lifecycle | cœur | Listing | Property/Media/advertiser lookups | publication events |
| MED | Media | cœur support | MediaCollection/MediaItem | upload et owner reference | media events |
| LEA | Contacts & Leads | cœur conversion | Lead | listing/advertiser eligibility | lead events |
| RSV | Reservations | cœur conditionnel | Reservation workflow | availability/account lookups | reservation events |
| MOD | Moderation & Reports | contrôle | ModerationCase | reports, catalog snapshots | decisions/commands |
| MON | Monetization & Payments | contrôle conditionnel | Product, Order, Payment, Benefit | provider callbacks, commands | financial events |
| SEA | Search & Discovery | projection | SearchIndex/read model | certified domain events | query results |
| SEO | Content & SEO | projection + contenu | Content/SeoProjection | editorial commands, events | pages, metadata, sitemap |
| FAV | Favorites | support utilisateur | FavoriteCollection | authenticated commands | private read model |
| NOT | Notifications | support delivery | Preference/DeliveryAttempt | domain events | email/SMS/WhatsApp delivery |
| ADM | Administration & Audit | contrôle | AdministrativeAction/AuditEntry | authorized admin commands | immutable audit |
| MIG | Legacy Migration | temporaire | checkpoints/evidence | legacy snapshots | idempotent import commands |
| PUB | Public Listing Projection | lecture certifiée | generated projection | routed lifecycle events | public page/source |

## 2. Relations autorisées

Notation : `A → B` signifie que A dépend d'un contrat de lecture ou consomme un
événement de B ; B ne dépend pas de A pour prendre sa décision.

- LST → REC, MED, PRO, IAM
- REC → GEO
- LEA → LST, PRO, IAM
- RSV → LST/REC, IAM
- MOD → LST, MED, PRO, IAM ; ses sanctions passent par les commandes de ces
  owners
- MON → IAM/PRO et référence éventuellement LST ; aucun pouvoir sur le ranking
- SEA → PUB et événements LST/REC/MED/GEO/PRO
- SEO → PUB, GEO et événements de visibilité
- FAV → PUB/LST en lecture
- NOT → événements de tous les producteurs et préférences IAM en lecture
- ADM → résultats de commandes de tous les domaines ; jamais leurs tables
- MIG → ports d'import de chaque owner ; jamais le runtime métier courant
- PUB → événements certifiés ; aucune écriture vers les producteurs

## 3. Sous-capacités par domaine

| Domaine | Agrégats/owners | Déjà acquis | À construire |
|---|---|---|---|
| IAM | Account | status lifecycle + historical persistence | auth, profile, recovery, closure, roles/consents HTTP |
| PRO | Professional | aggregate + status lifecycle | profile, verification, establishments, mandates, portfolio |
| GEO | Place | lifecycle complet | administration du référentiel et qualité/import |
| REC | Property | repository + lifecycle complet | authoring, taxonomy, validation, owner-facing API |
| LST | Listing | repository + publication complet | deposit/edit/portfolio and ownership |
| MED | MediaCollection, MediaItem | repository + remove/archive lifecycle | binary pipeline, variants, security, retention |
| LEA | Lead | eligibility + lifecycle complet | public ingress, channels, anti-abuse, advertiser delivery UX |
| RSV | Reservation | lifecycle complet | intake, availability, ownership, calendar |
| MOD | ModerationCase | domain/use cases/tests | toute la chaîne persistence/runtime/event/HTTP |
| MON | Product, Order, Payment, Benefit | domain/use cases/tests | décision commerciale puis chaîne complète |
| SEA | SearchIndex | decisions/projections | public query contract and experience |
| SEO | SeoProjection | projections, historical redirect/canonical | editorial CMS and operational SEO |
| FAV | FavoriteCollection | absent | bounded context complet |
| NOT | Preference, DeliveryAttempt | transport générique réutilisable | bounded context et adapters fournisseurs |
| ADM | AdministrativeAction | lifecycle complet | console/read model/command gateway |
| MIG | MigrationBatch/Checkpoint | enveloppe seulement | qualification, import, reconciliation, cutover |

## 4. Invariants de frontière

1. Les IDs externes sont des références, jamais des foreign aggregates
   modifiables.
2. Aucun domaine ne joint les tables privées d'un autre domaine pour décider.
3. Les read models SEA, SEO et PUB sont reconstruisibles et ne deviennent
   jamais autorités métier.
4. MOD émet une décision ; seul l'owner ciblé change son état.
5. MON octroie un bénéfice explicite ; LST décide comment une visibilité
   sponsorisée clairement étiquetée est représentée.
6. ADM autorise et trace ; il ne remplace pas l'Aggregate owner.
7. MIG disparaît du runtime après cutover.
8. Toute consommation d'une capacité gelée utilise son contrat V1 sans
   extension implicite.
