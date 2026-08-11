# F1 — Validation Report

## Campagnes

| Gate | Résultat terminal |
|---|---|
| Unit + Architecture ciblés | PASS — 9 tests, 194 assertions |
| PostgreSQL ciblé et intégration événementielle | PASS — 8 tests, 39 assertions |
| PHPStan ciblé | PASS — 0 erreur |
| Pint ciblé | PASS |
| `git diff --check` | PASS — exit code 0 |

## Scénarios PostgreSQL

- ingestion et replay ;
- pagination déterministe ;
- claim réel ;
- ledger réel ;
- `AlreadyApplied` ;
- `VersionConflict` ;
- optimistic locking ;
- savepoint et rollback externe.

## Intégrité

Migration 096 additive, rollback dédié. Aucune migration historique modifiée. Aucune commande métier BeginReview/Approve, aucune route et aucune UI créées.
