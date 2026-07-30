# Phase 5.3J — HTTP & Security Result Mapping

## Commands

Le payload public reste minimal : `status`, identifiant publiable lorsque
nécessaire, et `version` lorsque le contrat l'autorise. Aucun message technique.

| Résultat Application | HTTP candidat | Corps public |
|---|---:|---|
| `Applied` | 200 ; 201 pour création initiale | statut fermé, identifiant/version autorisés |
| `AlreadyApplied` | 200 | même forme terminale que l'application initiale |
| `DivergentIntent` | 409 | `conflict` |
| `VersionConflict` | 409 | `version_conflict` |
| `ForbiddenActor` | 403 | `forbidden` |
| `FourEyesViolation` | 403 | `forbidden` |
| `CaseMissing`, `ReportMissing`, `FindingMissing`, `ItemMissing` | 404 privé | `not_found` |
| `ReportNotAccepted`, `InsufficientEvidence`, `InvalidSupersession`, `NotClosable`, `Closed`, `LeaseConflict` | 409 | statut public fermé |
| `Rejected` | 422 | `rejected` |
| `DependencyUnavailable` | 503 | `temporarily_unavailable` |

La soumission publique homogénéise les erreurs de cible et de dépendance afin de
ne pas confirmer l'existence d'une ressource non possédée.

## Queries documentaires

Ces mappings sont normatifs mais non implémentables avant les readers V1.

| Query | Résultat | HTTP candidat |
|---|---|---:|
| Own report | `Visible` | 200 |
| Own report | `NotVisible` | 404 homogène |
| Own report | `DependencyUnavailable` | 503 |
| Queue | `Available` | 200 |
| Queue | `Empty` | 200 avec collection vide |
| Queue | `ForbiddenActor` | 403 |
| Queue | `InvalidCursor` | 422 |
| Queue | `QueueUnavailable`, `DependencyUnavailable` | 503 |
| Case/Decision | `Found` | 200 |
| Case/Decision | `Missing` | 404 |
| Case/Decision | `ForbiddenActor` | 403 |
| Case/Decision | `Corrupted`, `DependencyUnavailable` | 503 |

## Validation transport

- JSON object uniquement ;
- Content-Type JSON pour mutations ;
- UUID stricts pour identifiants et Idempotency-Key ;
- dates ISO-8601 avec fuseau ;
- listes bornées, non vides lorsque le Command l'exige ;
- catalogues `category`, `disposition`, `findingCode`, `targetAction`,
  `closureCode`, `policyVersion` fermés ;
- aucun champ inconnu ;
- aucune valeur libre utilisée comme diagnostic.

## En-têtes

- `Cache-Control: no-store` ;
- `X-Content-Type-Options: nosniff` ;
- `ETag` uniquement si une Query certifiée expose la version publiable ;
- aucun header interne de diagnostic.
