# Phase 5.3B — Closed Result Matrix

## Résultats des Commands

| Command V1 | Catalogue fermé |
|---|---|
| `SubmitModerationReportV1` | `Applied`, `AlreadyApplied`, `DivergentIntent`, `InvalidInput`, `TargetUnavailable`, `DependencyUnavailable`, `Rejected` |
| `ValidateModerationReportV1` | `Applied`, `AlreadyApplied`, `DivergentIntent`, `ReportMissing`, `ForbiddenActor`, `InvalidDisposition`, `VersionConflict`, `Closed`, `DependencyUnavailable`, `Rejected` |
| `RecordModerationFindingV1` | `Applied`, `AlreadyApplied`, `DivergentIntent`, `CaseMissing`, `ReportMissing`, `ReportNotAccepted`, `InsufficientEvidence`, `ForbiddenActor`, `VersionConflict`, `Closed`, `DependencyUnavailable`, `Rejected` |
| `IssueModerationDecisionV1` | `Applied`, `AlreadyApplied`, `DivergentIntent`, `CaseMissing`, `FindingMissing`, `InsufficientEvidence`, `ForbiddenActor`, `FourEyesViolation`, `InvalidSupersession`, `VersionConflict`, `Closed`, `TargetUnavailable`, `DependencyUnavailable`, `Rejected` |
| `CloseModerationCaseV1` | `Applied`, `AlreadyApplied`, `DivergentIntent`, `CaseMissing`, `NotClosable`, `ForbiddenActor`, `VersionConflict`, `Closed`, `DependencyUnavailable`, `Rejected` |
| `ClaimModerationQueueItemV1` | `Claimed`, `AlreadyClaimed`, `DivergentIntent`, `ItemMissing`, `LeaseConflict`, `InvalidLease`, `ForbiddenActor`, `QueueUnavailable`, `DependencyUnavailable`, `Rejected` |

## Résultats des Queries

| Query V1 | Catalogue fermé |
|---|---|
| `ReadOwnModerationReportV1` | `Visible`, `NotVisible`, `DependencyUnavailable` |
| `ReadModerationQueueV1` | `Available`, `Empty`, `ForbiddenActor`, `InvalidCursor`, `QueueUnavailable`, `DependencyUnavailable` |
| `ReadModerationCaseV1` | `Found`, `Missing`, `ForbiddenActor`, `Corrupted`, `DependencyUnavailable` |
| `ReadModerationDecisionV1` | `Found`, `Missing`, `ForbiddenActor`, `Corrupted`, `DependencyUnavailable` |

## Résultats des ports externes

| Port | Catalogue fermé |
|---|---|
| Target eligibility read | `Eligible`, `Ineligible`, `Missing`, `Corrupted`, `DependencyUnavailable` |
| Target action request | `Applied`, `AlreadyApplied`, `DivergentIntent`, `Rejected`, `TargetMissing`, `VersionConflict`, `DependencyUnavailable` |
| Audit append | `Applied`, `AlreadyApplied`, `DivergentRecord`, `Rejected`, `DependencyUnavailable` |
| Authorization decision | `Allowed`, `Denied`, `Corrupted`, `DependencyUnavailable` |

## Sémantique normative

- `Applied` : mutation entièrement commitée une seule fois.
- `Claimed` : lease entièrement commitée.
- `AlreadyApplied` : même identité et même checksum déjà terminal.
- `DivergentIntent` : même intent, checksum différent.
- `VersionConflict` : version attendue différente de la version courante.
- `ForbiddenActor` : acteur non habilité ou séparation directe violée.
- `FourEyesViolation` : indépendance globale des acteurs insuffisante.
- `InvalidSupersession` : décision remplacée absente ou non courante.
- `InsufficientEvidence` : préconditions probatoires non satisfaites.
- `Closed` : dossier terminal, aucune écriture réalisée.
- `DependencyUnavailable` : dépendance nécessaire non fiable ; fail-closed.
- `Rejected` : rejet propriétaire fermé ne révélant aucun diagnostic technique.

## Interdictions

- aucun booléen ;
- aucun code SQLSTATE ;
- aucune `PDOException` ou exception Laravel ;
- aucun message d'erreur libre comme discriminant ;
- aucun résultat extensible par chaîne arbitraire ;
- aucune assimilation de `Missing`, `Corrupted` et indisponibilité.
