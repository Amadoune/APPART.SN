# Validation Report

## Gates

| Gate | Résultat terminal |
|---|---|
| Unit + Architecture ciblés | PASS — 3 tests, 39 assertions |
| Feature composition ciblée | PASS — 1 test, 2 assertions |
| PostgreSQL ciblé | PASS — 3 tests, 13 assertions |
| PHPStan ciblé | PASS — 0 erreur |
| Pint ciblé | PASS |
| `git diff --check` | PASS — exit code 0 |

## Scénarios

- BeginReview et ApproveAndPublish réels ;
- workflow, Aggregate et Registry synchronisés ;
- MediaCollection résolue en interne ;
- expiration et révision déterministes ;
- `AlreadyApplied` et `DivergentCommand` ;
- ledger durable et rollback externe préservé ;
- migration 097 réversible.

PublicationReview F1, migration 096, Projection, Search et IAM ne sont pas modifiés par la Gateway.
