# Phase 5.3F — Orchestration & Four-Eyes Foundation

## Statut proposé

`GO PROPOSÉ`

## Orchestrateur propriétaire

`ModerationCaseOrchestratorV1` exécute exclusivement :

- `SubmitModerationReportV1` ;
- `ValidateModerationReportV1` ;
- `RecordModerationFindingV1` ;
- `IssueModerationDecisionV1` ;
- `CloseModerationCaseV1` ;
- `ClaimModerationQueueItemV1`.

Sa seule dépendance de composition est `ModerationRuntimeV1`, qui expose les
stores Case/Decision et le Runtime Queue certifiés.

## Quatre yeux

Les contrôles sont effectués avant toute écriture :

- reporter et validateur distincts ;
- un validateur ne peut être l'auteur unique du constat fondé sur ses propres
  validations ;
- l'auteur d'un constat ne peut décider ;
- tout acteur ayant reporté ou validé dans le dossier est exclu de la décision
  finale.

Les violations convergent vers `ForbiddenActor` ou `FourEyesViolation`.

## Idempotence et concurrence

Les mutations Case utilisent le journal `case_intents` certifié en 5.3D. Le
claim utilise exclusivement le journal Queue de l'amendement
`A-5.3-MODERATION-QUEUE-IDEMPOTENCE-01`.

- replay identique : `AlreadyApplied` ;
- intent divergent : `DivergentIntent` ;
- version obsolète : `VersionConflict` ;
- deux processus sur la même version : un `Applied`, un `VersionConflict`.

Les checksums sont canoniques, couvrent toutes les propriétés sémantiques et
normalisent explicitement les horodatages.

## Append-only et atomicité

- rapports, constats et décisions restent append-only ;
- toute nouvelle décision supersède explicitement la décision courante ;
- transaction locale uniquement ;
- advisory lock propriétaire ;
- savepoint en transaction englobante ;
- rollback intégral sans intent ni révision partiels.

## Hors périmètre

Aucun IAM, Listing, Media, Account, Professional, Administration Audit, Reader,
Gateway, handoff, HTTP, Event, Delivery, Consumer ou Outbox n'est consommé.

Les migrations 063 et 064, Runtime Health et les six amendements 5.3C restent
inchangés.
