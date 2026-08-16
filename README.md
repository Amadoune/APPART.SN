# APPART.SN REBUILD 2026

APPART-REBUILD est un monolithe modulaire DDD sous PHP 8.5, Laravel 13 et
PostgreSQL 18.x. La référence d'architecture et de planification en vigueur est
la baseline Phase 5.0, et non plus la baseline historique Phase 2.

## Statut officiel

```text
Phase 5.0A — Global Domain Audit
→ GO CERTIFIÉ
→ FERMÉE

Phase 5.0B — Baseline Alignment & Governance
→ GO CERTIFIÉ
→ FERMÉE

A-5.1-IAM-01 — Account Frozen Boundary Amendment
→ NO GO CERTIFIÉ
→ FERMÉ

A-5.1-IAM-PROFILE-01
→ GO CERTIFIÉ
→ FERMÉ

A-5.1-IAM-CLOSURE-01
→ GO CERTIFIÉ
→ FERMÉ

Phase 5.1 — Identity & Access Completion
→ GO FINAL CERTIFIÉ
→ FERMÉE
→ GELÉE

Phase 5.2A — Property & Listing Authoring
→ GO FINAL CERTIFIÉ
→ FERMÉE
→ GELÉE

Phase 5.2B — Media Ingestion
→ GO FINAL CERTIFIÉ
→ FERMÉE
→ GELÉE

Phase 5.2C — Professional Profile
→ DISCOVERY / BLUEPRINT GO CERTIFIÉ, FERMÉ
→ CONTRACTS FOUNDATION GO CERTIFIÉE, FERMÉE
→ PERSISTENCE FOUNDATION GO CERTIFIÉE, FERMÉE
→ RUNTIME FOUNDATION GO CERTIFIÉE, FERMÉE
→ HTTP FOUNDATION NO GO CERTIFIÉ, OUVERTE
→ SEUL JALON AUTORISÉ

A-5.2B-MEDIA-ATTACHMENT-BOUNDARY-01
→ GO CERTIFIÉ
→ FERMÉ
```

Le plan directeur officiel est
[`docs/PHASE-5.0-ROADMAP.md`](docs/PHASE-5.0-ROADMAP.md). La carte des domaines,
leurs owners et leurs dépendances sont définis par les six livrables 5.0A.

L'audit A-5.1-IAM-01 est clos NO GO. Les modèles User Profile/Identity Claim
et Account Closure sont GO certifiés. 5.1A à 5.1J sont GO certifiés et fermés.
5.1C Persistence est GO certifiée et fermée.
5.1D Profile Claims Seed & Authority Cutover est GO CERTIFIÉE et FERMÉE.
5.1E Runtime Composition & Availability Policy est GO CERTIFIÉE et FERMÉE.
5.1F Runtime Orchestration / Atomic Operations est GO CERTIFIÉE et FERMÉE.
5.1G Event Contracts, Transport, Routing & Delivery est GO CERTIFIÉE et FERMÉE.
5.1H Outbox Owner & Atomic Event Integration est GO CERTIFIÉE et FERMÉE.
5.1I HTTP Runtime & Security est GO CERTIFIÉE et FERMÉE.
`A-5.1-IAM-OUTBOX-CONCURRENCY-01` est GO CERTIFIÉ et FERMÉ. La réserve
concurrente de l'Outbox IAM est levée et la recertification d'impact de 5.1H
est satisfaite. Phase 5.1 est GO FINAL CERTIFIÉE, FERMÉE et GELÉE. F-17 et
F-18 sont actifs. Les jalons Discovery, Contracts, Implementation, Persistence,
Runtime, HTTP, Operations et Public Integration de 5.2A sont GO CERTIFIÉS et
fermés. Phase 5.2A est GO FINAL CERTIFIÉE, FERMÉE et GELÉE. F-19 et F-20
sont actifs et exécutoires. Le Discovery 5.2B est GO CERTIFIÉ et FERMÉ.
Contracts Foundation, l’amendement Media Attachment et Persistence Foundation
sont GO CERTIFIÉS et FERMÉS. Les migrations additives 058–060 sont certifiées
dans la tranche 5.2B mais ne deviennent gelées qu’après son GO final.
Runtime Foundation, Event / Transport / Routing / Delivery Foundation et Atomic
Delivery / Outbox Integration Foundation sont GO CERTIFIÉES et FERMÉES. La
migration 060 et les migrations 058–060 sont gelées par le GO FINAL 5.2B.
F-21 et F-22 sont actifs. Professional Profile Persistence et Runtime
Foundations sont GO CERTIFIÉES et FERMÉES ; HTTP Foundation reste ouverte en
NO GO CERTIFIÉ. L’audit documentaire
`A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01` est NO GO CERTIFIÉ et FERMÉ.
`A-5.2C-PROFESSIONAL-STATUS-PUBLIC-READ-01` est GO CERTIFIÉ et FERMÉ.
`A-5.2C-PROFESSIONAL-MANDATE-RESOLUTION-01` est NO GO CERTIFIÉ et FERMÉ.
`A-5.2C-PROFESSIONAL-MANDATE-PUBLIC-RESOLUTION-01` est GO CERTIFIÉ et FERMÉ.
HTTP Foundation corrective reste ouverte en NO GO : les implémentations owner et
bindings des deux contrats ne sont pas disponibles. Le sprint Owner Read
Implementations & Runtime Bindings est ouvert et représenté NO GO : aucune
source owner de mandat ne permettait d’implémenter le resolver sans contourner
les interdictions. Ce sprint est NO GO CERTIFIÉ et FERMÉ. Professional Mandate
Owner Source Foundation est ouverte ; son implémentation est conforme et son GO
reste probatoire jusqu’à la preuve PostgreSQL terminale.
Aucun jalon suivant n’est ouvert.
La réserve
PostgreSQL globale Reservation Lifecycle reste
hors périmètre et ne constitue pas une régression Media Ingestion.
L'effacement irréversible reste hors périmètre.

## État réel du dépôt

- 12 modules métier implémentés et une enveloppe temporaire
  `LegacyMigration` ;
- 14 Aggregate Roots historiques ;
- migrations PostgreSQL 001 à 060 ;
- projection publique durable, reconstruisible et exposée en HTTP ;
- Runtime Health certifié `Healthy` sur 58 capacités ;
- neuf chaînes lifecycle complètes et gelées : Listing Publication, Property,
  Reservation, Lead, Professional Status, Media Item, Administrative Action,
  Place et Account Status ;
- Historical Redirect, Historical Canonical Qualification et Historical
  Account Persistence certifiés ;
- Outbox, inboxes, delivery, replay, atomicité et composition runtime certifiés
  pour les capacités qui les utilisent.
- Property & Listing Authoring opérationnel jusqu’au parcours UI/API privé,
  avec création Listing, draft, ownership, portfolio et handoff F-01.

Une chaîne lifecycle certifiée ne signifie pas que le parcours produit complet
du domaine est terminé. Les capacités restantes sont inventoriées dans
[`docs/PHASE-5.0A-GLOBAL-DOMAIN-AUDIT.md`](docs/PHASE-5.0A-GLOBAL-DOMAIN-AUDIT.md).

## Gouvernance

Toute capacité certifiée et gelée :

1. peut être consommée par son contrat publié ;
2. ne peut être modifiée, étendue ou réinterprétée sans amendement versionné ;
3. conserve un Aggregate Owner, Event Owner, Projection Owner, Outbox Owner et
   HTTP Owner uniques ;
4. interdit les écritures SQL cross-domain et les transactions métier
   multi-owner.

Les registres officiels sont :

- `docs/PHASE-5.0B-FROZEN-CAPABILITIES-REGISTER.md` ;
- `docs/PHASE-5.0B-AMENDMENT-REGISTER.md` ;
- `docs/PHASE-5.0B-GOVERNANCE.md` ;
- `docs/PHASE-5.0B-CERTIFICATION-RULES.md`.

## Baseline qualité

Baseline certifiée héritée de 4.9L et revalidée par suites non-PostgreSQL en
5.0B :

```text
Suite complète applicative : 2 733 tests, 52 475 assertions — PASS
Architecture              :   592 tests, 44 523 assertions — PASS
Runtime Health            : Healthy — 58 capacités
PHPStan/Larastan          : 0 erreur
Pint                       : PASS
git diff --check           : PASS
```

La campagne PostgreSQL certifiée reste une gate distincte, exécutée avec
`composer test:postgresql` dans un environnement PostgreSQL 18.x dédié. Les
préconditions, commandes et règles de comparaison sont dans
[`docs/PHASE-5.0B-QUALITY-BASELINE.md`](docs/PHASE-5.0B-QUALITY-BASELINE.md).

La base applicative locale (`appart_rebuild`) et la base des suites
destructives (`appart_test`) doivent toujours être distinctes. Les suites
exigent l'identité `APPART_APPLICATION_PG_DATABASE` et refusent tout reset
lorsque la base courante n'est pas explicitement test-only ou correspond à
la base applicative.

## Commandes

```bash
composer quality
composer test:architecture
composer test:postgresql
composer security:audit
git diff --check
git status --short
```

Un GO de phase exige les gates adaptées au périmètre, une preuve enregistrée
et l'absence de modification non autorisée. Un timeout, un skip ou une
indisponibilité d'environnement n'est jamais un PASS.
