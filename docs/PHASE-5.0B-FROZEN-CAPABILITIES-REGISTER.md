# Phase 5.0B — Frozen Capabilities Register

## 1. Registre

| ID | Capacité gelée | Périmètre protégé | Source de gel | Évolution |
|---|---|---|---|---|
| F-01 | Listing Publication Lifecycle | workflow, persistence, event, delivery, atomicité, HTTP | certifications Phase 4.1 / dossier Listing Publication | amendement versionné |
| F-02 | Property Lifecycle | chaîne workflow à HTTP | certifications Phase 4.2 / dossier Property Lifecycle | amendement versionné |
| F-03 | Reservation Lifecycle | chaîne workflow à HTTP | certifications Phase 4.3 / dossier Reservation Lifecycle | amendement versionné |
| F-04 | Lead Lifecycle & Eligibility | workflow, evidence, persistence, delivery, HTTP | certifications Phase 4.4 | amendement versionné |
| F-05 | Professional Status Lifecycle | workflow, context, persistence, delivery, HTTP | certifications Phase 4.5 | amendement versionné |
| F-06 | Media Item Lifecycle | workflow, context, persistence, delivery, HTTP | certifications Phase 4.6 | amendement versionné |
| F-07 | Administrative Action Lifecycle | chaîne certifiée complète | `PHASE-4.7-ADMINISTRATIVE-ACTION-LIFECYCLE-FINAL-CERTIFICATION.md` | amendement versionné |
| F-08 | Place Lifecycle | chaîne certifiée complète, migrations 038–040 | `PHASE-4.8-FINAL-CERTIFICATION.md` | amendement versionné |
| F-09 | Account Status Lifecycle | workflow, historical Account, runtime, event, transport, routing, delivery, Outbox, atomicité, HTTP, migrations 041–043 | GO FINAL 4.9 + `PHASE-4.9-FINAL-CERTIFICATION.md` | amendement préalable obligatoire |
| F-10 | Historical Account Persistence | Snapshot V1, repository, lookup et runtime source | `PHASE-4.9P-FINAL-CERTIFICATION.md` | amendement versionné |
| F-11 | Public Listing Projection | source, store, updater, worker, rebuild, reconciliation, public read | certifications Public Projection / Phases 3.x | amendement versionné |
| F-12 | Historical Redirect | décision, persistence, runtime et HTTP integration | dossier Historical Redirect | amendement versionné |
| F-13 | Historical Canonical Qualification | contrat, persistence et runtime | dossier Historical Canonical Qualification | amendement versionné |
| F-14 | Runtime Health Catalog | 58 requirements et composition certifiée | baseline finale 4.9L | phase runtime/amendement |
| F-15 | Generic Delivery/Outbox Foundation | enveloppe, ownership logique, transaction et compatibilités certifiées | dossiers Outbox/Delivery et certifications lifecycles | amendement versionné |
| F-16 | Migrations 001–043 certifiées | schémas, owners, ordering et rollback documentés | certifications PostgreSQL correspondantes | nouvelle migration additive ou amendement ; jamais réécriture |
| F-17 | Identity & Access Completion | Authentication, Attempts, Sessions, Recovery, Profile, Claims, Contact Changes, Revisions, Closure, Availability, Seed/Cutover, Runtime, orchestration, events V1, delivery, Outbox IAM et HTTP | GO FINAL 5.1 | amendement versionné préalable obligatoire |
| F-18 | Migrations IAM 044–054 | schéma `identity_access_completion`, mappings, ordering, rollback et garanties PostgreSQL | GO FINAL 5.1 | nouvelle migration additive ou amendement ; jamais réécriture |
| F-19 | Property & Listing Authoring | PropertyAuthoring, Listing Draft, Ownership, Portfolio, Creation V1, persistence, Runtime, HTTP, Operations et Public Integration | GO FINAL 5.2A | amendement versionné préalable obligatoire |
| F-20 | Migrations Authoring 055–057 | intents Listing, schémas Property/Listing Authoring, mappings, rollback et concurrence | GO FINAL 5.2A | nouvelle migration additive ou amendement ; jamais réécriture |
| F-21 | Media Ingestion | Upload, Asset, Processing, Quota, Attachment V1, persistence, Runtime, événements V1, transport, routing, delivery et Outbox | GO FINAL 5.2B | amendement versionné préalable obligatoire |
| F-22 | Migrations Media Ingestion 058–060 | schémas, mappings, ordering, rollback, concurrence et `message_id varchar(83)` | GO FINAL 5.2B | nouvelle migration additive ou amendement ; jamais réécriture |

## 2. Règles du gel

Le gel couvre le comportement, les contrats, les mappings, les migrations,
les owners et les garanties de test explicitement certifiés. Il ne transforme
pas automatiquement tout le module en capacité terminée.

Sont autorisés sans mutation :

- consommation du contrat publié ;
- nouveau consumer idempotent ;
- nouvelle documentation explicative non normative ;
- test de non-régression sans changement de comportement.

Exigent une revue, et souvent un amendement :

- nouveau champ ou event type ;
- nouvelle transition ou nouveau diagnostic ;
- changement de serializer, route ou code HTTP ;
- ajout d'un producteur dans l'Outbox commune ;
- modification d'un requirement Runtime Health ;
- modification d'une migration existante.

## 3. Gate spécifique 5.1

`Account`, `AccountRegistry`, Historical Account et Account Status sont
protégés par F-09/F-10. Identity & Access Completion commence par un dossier
d'amendement qui sépare les besoins d'authentification/profil des invariants
gelés et prouve qu'aucune extension implicite n'est nécessaire.

Depuis le GO FINAL 5.1, F-17 et F-18 sont exécutoires. L'anonymisation et
l'effacement irréversible restent absents du gel fonctionnel, car ils ne sont
pas implémentés ; leur ouverture exige `A-5.1-IAM-ERASURE-01`.

## 4. Gel exécutoire 5.2A

Depuis le GO FINAL 5.2A, F-19 et F-20 sont certifiés, actifs et exécutoires.
Toute évolution de Property & Listing Authoring exige un amendement versionné.
Les migrations 055–057 ne peuvent être réécrites ; une évolution de schéma
doit être additive ou préalablement autorisée par amendement.

## 5. Gel exécutoire 5.2B

Depuis le GO FINAL 5.2B, F-21 et F-22 sont certifiés, actifs et exécutoires.
Toute évolution de Media Ingestion exige un amendement versionné. Les migrations
058–060 ne peuvent être réécrites ; seule une migration additive ou un
amendement préalable est autorisé.
