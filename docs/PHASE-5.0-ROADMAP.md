# Phase 5.0 → Production — Roadmap directrice

## 1. Principes d'exécution

Cette roadmap est ordonnée par dépendances et non par ancienneté des documents.
Chaque capacité suit le même train : Discovery/Blueprint → contracts →
persistence → runtime → events/transport/routing/delivery → atomicité → HTTP →
certification finale → gel. Une étape peut être déclarée « non applicable »,
mais jamais sautée sans décision documentée.

Les Phases 5.1 à 5.8 forment le **MVP de production**. La monétisation est une
voie conditionnelle : son absence ne bloque pas le lancement si la décision
commerciale reste « différée ».

**État au 27 juillet 2026 :** 5.1A–5.1J sont GO CERTIFIÉS et fermés.
`A-5.1-IAM-OUTBOX-CONCURRENCY-01` est GO CERTIFIÉ et FERMÉ. Phase 5.1 est
GO FINAL CERTIFIÉE, FERMÉE et GELÉE. F-17 et F-18 sont actifs. Phase 5.2A
Property & Listing Authoring a certifié ses jalons Discovery à Public
Integration et obtenu son GO FINAL. Elle est FERMÉE et GELÉE ; F-19 et F-20
sont actifs et exécutoires. Le Discovery 5.2B est GO CERTIFIÉ et FERMÉ.
Contracts Foundation et l’amendement Media Attachment sont GO CERTIFIÉS et
FERMÉS. Persistence Foundation est GO CERTIFIÉE et FERMÉE. Runtime Foundation
est GO CERTIFIÉE et FERMÉE. Event / Transport / Routing / Delivery Foundation
est GO CERTIFIÉE et FERMÉE. Atomic Delivery / Outbox Integration Foundation est
GO CERTIFIÉE et FERMÉE. Phase 5.2B est GO FINAL CERTIFIÉE, FERMÉE et GELÉE.
Professional Profile Discovery / Blueprint, Contracts, Persistence et Runtime
Foundations sont GO CERTIFIÉS et FERMÉS. HTTP Foundation reste ouverte en
NO GO CERTIFIÉ. L’audit
`A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01` est NO GO CERTIFIÉ et FERMÉ.
`A-5.2C-PROFESSIONAL-STATUS-PUBLIC-READ-01` est GO CERTIFIÉ et FERMÉ.
`A-5.2C-PROFESSIONAL-MANDATE-RESOLUTION-01` est NO GO CERTIFIÉ et FERMÉ.
`A-5.2C-PROFESSIONAL-MANDATE-PUBLIC-RESOLUTION-01` est GO CERTIFIÉ et FERMÉ.
HTTP Foundation corrective reste NO GO faute d’implémentations owner et de
bindings autorisés. Le sprint Owner Read Implementations & Runtime Bindings est
NO GO CERTIFIÉ et FERMÉ. Professional Mandate Owner Source Foundation est
ouverte et attend sa preuve PostgreSQL terminale.
Aucun jalon suivant n’est ouvert.

## 2. Séquence officielle

| Phase | Capacité | Résultat attendu | Prérequis/Gate |
|---|---|---|---|
| 5.0A | Global Domain Audit | six livrables directeurs | GO FINAL 4.9 |
| 5.0B | Baseline Alignment & Governance | docs racine alignées, registre des gels/amendements, quality baseline reproductible | GO 5.0A |
| 5.1 | Identity & Access Completion | auth, recovery, profile, roles/consents, closure | amendement versionné 4.9 avant toute mutation |
| 5.2A | Property & Listing Authoring | taxonomie, dépôt, édition, ownership, portfolio | IAM minimal + lifecycles gelés consommables |
| 5.2B | Media Ingestion | upload sécurisé, storage, variants, quotas, retention | ownership Media/Listing/Property stabilisé |
| 5.2C | Professional Profile | profile, verification, establishments, mandates, public portfolio | IAM + Listing references |
| 5.3 | Moderation & Reports | report-to-decision, queues, command handoff, four-eyes, audit | 5.2A/B, IAM admin, Audit gelé |
| 5.4A | Lead Ingress & Contact Delivery | public contact, anti-abuse, consent, channel delivery | public listing + advertiser status |
| 5.4B | Reservation Intake & Availability | creation, availability, conflicts, ownership | Property/Listing authoring + IAM |
| 5.4C | Favorites | private favorite collection and APIs | IAM + public Listing |
| 5.5A | Search Experience | query API, filters, ranking policy, pagination, freshness SLO | PUB/Search sources certifiés |
| 5.5B | Editorial Content & Operational SEO | CMS, legal pages, sitemap, admin SEO | IAM roles + historical SEO gelé |
| 5.5C | Notifications | preferences, templates, provider adapters, retries | stable event catalogs 5.1–5.5B |
| 5.6 | Administration Console | safe command gateway, audit read model, operational queues | IAM, MOD, content and domain HTTP |
| 5.7 | Legacy Migration & Reconciliation | import rehearsals, quarantine, volume/content reconciliation, cutover plan | target contracts frozen |
| 5.8A | Security, Privacy & Compliance | threat model, secrets, retention, DSAR, abuse controls | all MVP flows stable |
| 5.8B | Reliability & Operations | observability, SLO, backup/restore, DR, queues, runbooks | runtime stable |
| 5.8C | Experience & Acceptance | responsive UI, accessibility, E2E, performance, UAT | APIs and content stable |
| 5.9 | Production Readiness Review | release candidate, migration rehearsal, rollback, sign-offs | 5.7 + 5.8 all GO |
| 5.10 | Cutover & Hypercare | production launch, monitored migration, rollback window, Legacy shutdown | GO FINAL 5.9 |
| 6.0 (conditionnelle) | Monetization & Payments | product/order/payment/benefit, reconciliation/refund | business GO, provider, legal/finance controls |

### Séquence terminale normative de la Phase 5.3

**État normatif courant au 30 juillet 2026 :**

1. 5.3I — Command Handoff Integration / Listing — GO CERTIFIÉ, FERMÉ ;
2. 5.3J — HTTP & Security — GO CERTIFIÉ, FERMÉ ;
3. 5.3K — Operational & Audit Certification — GO CERTIFIÉ, FERMÉ ;
4. 5.3L — Final Certification & Freeze — GO CERTIFIÉ, OUVERTE.

Les sources owner Report et Queue ainsi que les quatre HTTP Read Boundaries
sont certifiées et fermées. Les amendements Audit Append, Operational Audit
Event/Routing Coverage et Residual Operational Audit Coverage sont certifiés
et fermés. Aucun amendement technique 5.3 ne demeure ouvert.

Les paragraphes ci-dessous relatifs aux NO GO et ouvertures successives sont
conservés comme **HISTORICAL_ONLY**. Ils documentent la séquence ayant conduit à
l'état normatif courant et ne doivent pas être lus comme des statuts actifs.

L'alignement `A-5.3-ROADMAP-SEQUENCING-ALIGNMENT-01` conserve 5.3I comme
Command Handoff Integration / Listing, GO CERTIFIÉ et FERMÉ. La suite
prospective est :

1. 5.3J — HTTP & Security — Discovery NO GO CERTIFIÉ, FERMÉ ; implémentation
   interdite ;
2. 5.3K — Operational & Audit Certification — NON OUVERT ;
3. 5.3L — Final Certification & Freeze — NON OUVERT.

La séquence historique du Blueprint reste une preuve de planification initiale.
Elle est remplacée uniquement pour les jalons futurs. Aucun jalon n'est ouvert
par cet alignement.

L'ouverture explicite ultérieure de 5.3J ne vaut ni GO technique ni ouverture
de 5.3K/5.3L. Son Discovery identifie comme gate préalable recommandé
`A-5.3-MODERATION-HTTP-READ-BOUNDARIES-01`.

L'audit de ce gate est NO GO CERTIFIÉ et FERMÉ : les sources owner nécessaires
à la résolution par reportId et à la lecture paginée de la queue sont absentes.
Le Discovery `A-5.3-MODERATION-REPORT-OWNER-READ-SOURCE-01` est GO CERTIFIÉ et
FERMÉ. Sa Contracts & Implementation Foundation est désormais
**GO CERTIFIÉE — FERMÉE — GELÉE**. La campagne
PostgreSQL complète terminale est désormais reconnue PASS avec 658 tests et
2 935 assertions ; la réserve PostgreSQL est levée. La gate Pint globale est
également levée par l'amendement 5.3G GO CERTIFIÉ, FERMÉ et GELÉ.
`A-5.3-MODERATION-QUEUE-OWNER-READ-SOURCE-01` reste une
frontière distincte, IDENTIFIÉE et NON OUVERTE.

L'audit documentaire `A-5.3-GLOBAL-PROOF-QUALIFICATION` qualifie les deux
réserves comme étrangères au comportement ciblé Report mais bloquantes pour le
GO selon les règles actuelles : PostgreSQL complet terminal reste obligatoire,
et Pint global ne peut être neutralisé par une simple réserve.

Le diagnostic `A-5.3-POSTGRESQL-FULL-CAMPAIGN-DIAGNOSTIC-01` est approuvé et
clos. Le rejeu dédié a produit un résultat terminal après 743,801 secondes :
658 tests, 2 935 assertions, zéro failure, zéro erreur et exit code 0.

La qualification `A-5.3-PINT-GLOBAL-GATE-QUALIFICATION-01` est approuvée et
close. L'amendement 5.3G strictement limité au formatage est GO CERTIFIÉ, FERMÉ
et GELÉ ; Pint global produit PASS avec exit code 0. Aucune réserve globale ne
subsiste sur la fondation Report.

Ce verdict n'ouvre ni la source Queue, ni HTTP, ni 5.3K, ni 5.3L. Le prochain
jalon autorisable doit être déterminé par une nouvelle décision d'autorité.

## 3. Lots parallélisables sans réorganisation

- Après 5.1, 5.2A et 5.2C peuvent avancer en parallèle ; 5.2B démarre dès que
  le contrat d'ownership de 5.2A est gelé.
- 5.4A, 5.4B et 5.4C peuvent avancer en parallèle après leurs gates.
- 5.5A et 5.5B peuvent avancer en parallèle. 5.5C attend la stabilisation de
  leurs événements.
- La préparation sécurité/ops commence en continu, mais ses certifications
  finales restent 5.8A/B.
- 6.0 peut être lancé après décision commerciale sans déplacer le chemin MVP ;
  il ne doit pas être introduit au milieu du chemin critique.

## 4. Gates par phase

| Gate | Critères minimaux |
|---|---|
| Discovery GO | owner unique, invariants, scope/non-scope, dependencies, read/write, PII, risks |
| Contract GO | ports, commands/results, events/version, idempotence, errors, consumers |
| Persistence GO | schema owner, mapper, migration/down, rollback, concurrency, retention |
| Runtime GO | bindings, health, transaction boundary, no cross-domain SQL |
| Delivery GO | outbox owner, inbox, retry/quarantine/replay, observability |
| HTTP GO | authn/authz, validation, status/error contract, rate limit, no leaked diagnostics |
| Final GO | unit/architecture/PostgreSQL/feature/E2E green, docs, runbook, residual risks accepted |

## 5. Définition du MVP de production

Le lancement exige au minimum :

- compte sécurisé et parcours de récupération/fermeture ;
- dépôt, édition, médias et portefeuille annonceur ;
- profils professionnels ;
- modération et signalement ;
- recherche, fiche publique, géographie et SEO ;
- contacts/leads ; réservation seulement si confirmée comme promesse produit ;
- favoris ;
- contenus légaux, consentements et privacy operations ;
- administration sûre et audit ;
- migration Legacy réconciliée ;
- monitoring, backup/restore, sécurité, accessibilité, performance et runbooks.

Monétisation, social login, multilingue, publicité, carte avancée et analytics
professionnels restent conditionnels et ne doivent pas retarder le MVP sans
nouvelle décision d'autorité.

## 6. Critères GO FINAL vers production

1. zéro dépendance runtime à Legacy ;
2. rapprochement de migration signé par domaine ;
3. aucune écriture cross-domain et owners uniques vérifiés ;
4. toutes les capacités gelées inchangées ou amendées/recertifiées ;
5. événements, retries, quarantaines et replays observables ;
6. restauration testée et RPO/RTO acceptés ;
7. scans sécurité et privacy gates sans risque critique ouvert ;
8. SLO de disponibilité et fraîcheur tenus sur charge cible ;
9. parcours essentiels accessibles et validés UAT ;
10. plan de cutover, rollback et hypercare exécutable.

## 7. Décision 5.0A

La roadmap est **proposée GO** comme plan directeur officiel. Ce verdict est
documentaire : il autorise uniquement l'ouverture de 5.0B, pas une
implémentation métier anticipée.
