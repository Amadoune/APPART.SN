# Roadmap APPART.SN REBUILD 2026

## Autorité

La roadmap détaillée et normative est
[`docs/PHASE-5.0-ROADMAP.md`](docs/PHASE-5.0-ROADMAP.md). Ce document racine en
est le pointeur synthétique officiel.

## État

| Jalon | Statut |
|---|---|
| Phases 2 et 3 — fondations, persistence et projection publique | certifiées et historiques |
| Phases 4.1 à 4.9 — neuf lifecycles | certifiées, fermées et gelées |
| Phase 5.0A — Global Domain Audit | GO CERTIFIÉ, FERMÉE |
| Phase 5.0B — Baseline Alignment & Governance | GO CERTIFIÉ, FERMÉE |
| A-5.1-IAM-01 — Account Frozen Boundary Amendment | NO GO CERTIFIÉ, FERMÉ |
| A-5.1-IAM-PROFILE-01 | GO CERTIFIÉ, FERMÉ |
| A-5.1-IAM-CLOSURE-01 | GO CERTIFIÉ, FERMÉ |
| A-5.1-IAM-OUTBOX-CONCURRENCY-01 | GO CERTIFIÉ, FERMÉ |
| Phase 5.1 — Identity & Access Completion | GO FINAL CERTIFIÉE, FERMÉE, GELÉE |
| Phase 5.2A — Property & Listing Authoring | GO FINAL CERTIFIÉE, FERMÉE, GELÉE |
| Phase 5.2B — Media Ingestion | GO FINAL CERTIFIÉE, FERMÉE, GELÉE |
| Phase 5.2C — Professional Profile | Discovery, Contracts, Persistence et Runtime GO CERTIFIÉS, FERMÉS ; HTTP Foundation NO GO CERTIFIÉ, OUVERTE |
| A-5.2C-PROFESSIONAL-STATUS-READ-BOUNDARY-01 | NO GO CERTIFIÉ, FERMÉ |
| A-5.2C-PROFESSIONAL-STATUS-PUBLIC-READ-01 | GO CERTIFIÉ, FERMÉ |
| A-5.2C-PROFESSIONAL-MANDATE-RESOLUTION-01 | NO GO CERTIFIÉ, FERMÉ |
| A-5.2C-PROFESSIONAL-MANDATE-PUBLIC-RESOLUTION-01 | GO CERTIFIÉ, FERMÉ |
| Phase 5.2C — Owner Read Implementations & Runtime Bindings | NO GO CERTIFIÉ, FERMÉ |
| Phase 5.2C — Professional Mandate Owner Source Foundation | OUVERTE ; implémentation conforme, PostgreSQL terminal en attente |
| A-5.2B-MEDIA-ATTACHMENT-BOUNDARY-01 | GO CERTIFIÉ, FERMÉ |

## Trajectoire autorisée

1. 5.2B — Media Ingestion

`A-5.1-IAM-ERASURE-01` reste hors de cette séquence et n'est pas bloquant pour
5.1. Il devient obligatoire uniquement avant toute anonymisation ou destruction
de Historical Account.
2. 5.2C — Professional Profile
3. 5.3 — Moderation & Reports

Séquence terminale normative de 5.3 :

- 5.3I — Command Handoff Integration / Listing : GO CERTIFIÉ, FERMÉ ;
- 5.3J — HTTP & Security : GO CERTIFIÉ, FERMÉ ;
- 5.3K — Operational & Audit Certification : GO CERTIFIÉ, FERMÉ ;
- 5.3L — Final Certification & Freeze : GO CERTIFIÉ, OUVERTE ;
- A-5.3-MODERATION-HTTP-READ-BOUNDARIES-01 : GO CERTIFIÉ, FERMÉ ;
- source owner Report : GO CERTIFIÉE, FERMÉE et GELÉE ;
- source owner Queue : GO CERTIFIÉE, FERMÉE ;
- audit global des preuves : réserves PostgreSQL et Pint levées ;
- qualification Pint globale : APPROUVÉE, CLOSE ; amendement 5.3G GO CERTIFIÉ,
  FERMÉ et GELÉ ; Pint global PASS ;
- diagnostic PostgreSQL complet : APPROUVÉ, CLOS ; campagne terminale 658 tests,
  2 935 assertions, zéro failure, zéro erreur, exit code 0 ;
- A-5.3-AUDIT-APPEND-BOUNDARY-01 : GO CERTIFIÉ, FERMÉ ;
- A-5.3-MODERATION-OPERATIONAL-AUDIT-EVENT-ROUTING-COVERAGE-01 :
  GO CERTIFIÉ, FERMÉ ;
- A-5.3-MODERATION-RESIDUAL-OPERATIONAL-AUDIT-COVERAGE-01 :
  GO CERTIFIÉ, FERMÉ.

Les anciens NO GO de 5.3J, des HTTP Read Boundaries et de 5.3K sont conservés
dans le CHANGELOG comme décisions historiques. Ils ne représentent plus l'état
normatif courant.

Cette séquence remplace prospectivement la numérotation terminale du Blueprint
initial sans renommer ni réinterpréter un jalon déjà certifié.
4. 5.4 — Lead Ingress, Reservation Intake, Favorites
5. 5.5 — Search Experience, Content/SEO, Notifications
6. 5.6 — Administration Console
7. 5.7 — Legacy Migration & Reconciliation
8. 5.8 — Security, Privacy, Reliability, Experience
9. 5.9 — Production Readiness Review
10. 5.10 — Cutover & Hypercare

La monétisation constitue une voie conditionnelle Phase 6.0. Elle requiert un
GO commercial, juridique et financier et ne bloque pas le chemin MVP tant que
la décision officielle reste « différée ».

## Règle d'ouverture

Une seule phase séquentielle est ouverte à la fois. Les sous-lots explicitement
parallélisables par la roadmap normative peuvent coexister après leurs gates.
Aucune capacité gelée ne peut être modifiée pour préparer une phase future
sans amendement versionné certifié.
